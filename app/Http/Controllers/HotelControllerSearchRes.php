<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use App\Models\HotelData;
use Carbon\Carbon;
use App\Models\TBOHotelCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\HotelBookingConfirmation;

use Illuminate\Support\Facades\Log;
class HotelControllerSearchRes extends Controller
{



    public function searchHotels(Request $request)
    {
        $validated = $request->validate([
            'cityCode' => 'required|string',
            'checkIn' => 'required|date',
            'checkOut' => 'required|date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'childrenAges' => 'nullable|array',
            'childrenAges.*' => 'nullable|integer|min:0|max:17',
            'guestNationality' => 'required|string',
        ]);

        $hotelresult = [];

        // Check database for valid hotel data (within 15 days)
        $dbHotels = HotelData::where('city_code', $validated['cityCode'])
            ->where('created_at', '>=', Carbon::now()->subDays(15))
            ->get();

        if ($dbHotels->isNotEmpty()) {
            foreach ($dbHotels as $hotel) {
                $hotelresult[] = [
                    'hotelDetails' => $hotel->hotel_details,
                    'searchResults' => $hotel->search_results,
                ];
            }

            return response()->json([
                'totalHotels' => $hotelresult,
            ]);
        }

        // Check TBOHotelCode table for valid hotel codes (within 15 days)
        $dbHotelCodes = TBOHotelCode::where('city_code', $validated['cityCode'])
            ->where('created_at', '>=', Carbon::now()->subDays(15))
            ->get();

        $hotelCodes = [];
        if ($dbHotelCodes->isNotEmpty()) {
            $hotelCodes = $dbHotelCodes->pluck('hotel_code')->toArray();
        } else {
            // No valid data in TBOHotelCode, fetch from API
            $client = new Client();
            $startTime = microtime(true);
            $response1 = $client->post('http://api.tbotechnology.in/TBOHolidays_HotelAPI/TBOHotelCodeList', [
                'auth' => ['TBOStaticAPITest', 'Tbo@11530818'],
                'json' => [
                    "CityCode" => $validated['cityCode'],
                    "IsDetailedResponse" => true,
                ]
            ]);
            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            Log::info('HotelCodeList API Request', [
                'cityCode' => $validated['cityCode'],
                'executionTimeMs' => $executionTime,
            ]);

            $hotelData = json_decode($response1->getBody()->getContents(), true);
            $hotels = $hotelData['Hotels'] ?? [];

            if (empty($hotels)) {
                return response()->json([
                    'message' => 'No hotels available',
                    'totalHotels' => [],
                ]);
            }

            // Save hotel codes to TBOHotelCode table
            foreach ($hotels as $hotel) {
                TBOHotelCode::updateOrCreate(
                    [
                        'hotel_code' => $hotel['HotelCode'],
                        'city_code' => $validated['cityCode'],
                    ],
                    [
                        'hotel_name' => $hotel['HotelName'] ?? null,
                        'latitude' => $hotel['Latitude'] ?? null,
                        'longitude' => $hotel['Longitude'] ?? null,
                        'hotel_rating' => $hotel['StarRating'] ?? null,
                        'address' => $hotel['Address'] ?? null,
                        'country_name' => $hotel['CountryName'] ?? null,
                        'country_code' => $hotel['CountryCode'] ?? null,
                        'city_name' => $hotel['CityName'] ?? null,
                    ]
                );
            }

            $hotelCodes = array_column($hotels, 'HotelCode');
        }

        $hotelCodes = array_slice($hotelCodes, 0, 1000); // Limit to 1000 hotel codes for processing
        if (empty($hotelCodes)) {
            return response()->json([
                'message' => 'No hotels available',
                'totalHotels' => [],
            ]);
        }

        // Split hotel codes into chunks of 100
        $hotelCodeChunks = array_chunk($hotelCodes, 100);
        $client = new Client();
        $requests = function ($hotelCodeChunks) use ($validated, $client) {
            foreach ($hotelCodeChunks as $index => $chunk) {
                $hotelCodesString = implode(',', $chunk);
                $payload = [
                    "CheckIn" => $validated['checkIn'],
                    "CheckOut" => $validated['checkOut'],
                    "HotelCodes" => $hotelCodesString,
                    "GuestNationality" => $validated['guestNationality'],
                    "PaxRooms" => [
                        [
                            "Adults" => $validated['adults'],
                            "Children" => $validated['children'],
                            "ChildrenAges" => $validated['children'] > 0 ? ($validated['childrenAges'] ?? array_fill(0, $validated['children'], null)) : null,
                        ]
                    ],
                    "ResponseTime" => 23,
                    "IsDetailedResponse" => true,
                    "Filters" => [
                        "Refundable" => false,
                        "NoOfRooms" => 1,
                        "MealType" => 0,
                        "OrderBy" => 0,
                        "StarRating" => 0,
                        "HotelName" => null,
                    ]
                ];

                yield new GuzzleRequest('POST', 'https://affiliate.tektravels.com/HotelAPI/Search', [
                    'Authorization' => 'Basic ' . base64_encode('Apkatrip:Apkatrip@1234'),
                    'Content-Type' => 'application/json',
                ], json_encode($payload));
            }
        };

        $hotelresult = [];
        $pool = new Pool($client, $requests($hotelCodeChunks), [
            'concurrency' => 5, // Adjust concurrency level as needed
            'fulfilled' => function ($response, $index) use (&$hotelresult, $hotelCodeChunks, $client, $validated) {
                $startTime = microtime(true);
                $searchResults = json_decode($response->getBody()->getContents(), true);
                $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
                Log::info('Hotel Search API Response for Chunk', [
                    'chunkIndex' => $index,
                    'hotelCodes' => implode(',', $hotelCodeChunks[$index]),
                    'executionTimeMs' => $executionTime,
                    'response' => $searchResults,
                ]);

                // Check if the response indicates no available rooms globally
                if (isset($searchResults['Status']['Code']) && $searchResults['Status']['Code'] === 201 && $searchResults['Status']['Description'] === "No Available rooms for given criteria") {
                    return;
                }

                // Process search results for each hotel
                $hotels = $searchResults['HotelResult'] ?? $searchResults['HotelResults'] ?? [];
                foreach ($hotels as $hotelResult) {
                    $hotelCode = $hotelResult['HotelCode'] ?? null;
                    if (!$hotelCode) {
                        Log::warning('Missing HotelCode in search result', [
                            'chunkIndex' => $index,
                            'hotelResult' => $hotelResult,
                        ]);
                        continue;
                    }

                    // Check if hotel data already exists in HotelData
                    $existingHotel = HotelData::where('hotel_code', $hotelCode)
                        ->where('city_code', $validated['cityCode'])
                        ->where('created_at', '>=', Carbon::now()->subDays(15))
                        ->first();

                    if ($existingHotel && !empty($existingHotel->search_results['Rooms']) && is_array($existingHotel->search_results['Rooms'])) {
                        $hotelresult[] = [
                            'hotelDetails' => $existingHotel->hotel_details,
                            'searchResults' => array_merge(
                                ['Status' => $searchResults['Status'] ?? ['Code' => 200, 'Description' => 'Successful']],
                                $existingHotel->search_results
                            ),
                        ];
                        continue;
                    }

                    // Fetch hotel details
                    try {
                        $detailStartTime = microtime(true);
                        $response2 = $client->post('http://api.tbotechnology.in/TBOHolidays_HotelAPI/Hoteldetails', [
                            'auth' => ['TBOStaticAPITest', 'Tbo@11530818'],
                            'json' => [
                                "Hotelcodes" => $hotelCode,
                                "Language" => "EN",
                            ]
                        ]);
                        $detailExecutionTime = (microtime(true) - $detailStartTime) * 1000; // Convert to milliseconds
                        Log::info('Hotel Details API Request', [
                            'hotelCode' => $hotelCode,
                            'executionTimeMs' => $detailExecutionTime,
                        ]);

                        $hotelDetails = json_decode($response2->getBody()->getContents(), true);

                        if (!empty($hotelResult['Rooms']) && is_array($hotelResult['Rooms'])) {
                            $hotelresult[] = [
                                'hotelDetails' => $hotelDetails,
                                'searchResults' => array_merge(
                                    ['Status' => $searchResults['Status'] ?? ['Code' => 200, 'Description' => 'Successful']],
                                    $hotelResult
                                ),
                            ];

                            // Save to HotelData
                            HotelData::updateOrCreate(
                                [
                                    'hotel_code' => $hotelCode,
                                    'city_code' => $validated['cityCode'],
                                ],
                                [
                                    'hotel_details' => $hotelDetails,
                                    'search_results' => $hotelResult,
                                ]
                            );
                        }
                    } catch (\Exception $e) {
                        Log::error('Hotel Details API Error', [
                            'hotelCode' => $hotelCode,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            },
            'rejected' => function (RequestException $reason, $index) use ($hotelCodeChunks) {
                Log::error('Hotel Search API Error for Chunk', [
                    'chunkIndex' => $index,
                    'hotelCodes' => implode(',', $hotelCodeChunks[$index]),
                    'error' => $reason->getMessage(),
                ]);
            },
        ]);

        // Execute the pool of requests
        $promise = $pool->promise();
        $promise->wait();

        if (empty($hotelresult)) {
            Log::warning('No hotels added to hotelresult', [
                'hotel_count' => count($hotels),
                'validated_input' => $validated,
                'hotel_codes' => $hotelCodes,
            ]);
            return response()->json([
                'message' => 'No hotels available',
                'statusCode' => 200,
                'totalHotels' => [],
            ]);
        }

        return response()->json([
            'statusCode' => 200,
            'totalHotels' => $hotelresult,
        ]);
    }


   public function singleHotelget(Request $request)
    {
        $validated = $request->validate([
            'HotelCode' => 'required|string',
            'checkIn' => 'required|date',
            'checkOut' => 'required|date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'guestNationality' => 'required|string',


        ]);
        $client = new \GuzzleHttp\Client();


        $response1 = Http::withBasicAuth('TBOStaticAPITest', 'Tbo@11530818')->post('http://api.tbotechnology.in/TBOHolidays_HotelAPI/Hoteldetails', [
            "Hotelcodes" => $validated['HotelCode'],
            "Language" => "EN"
        ]);

        $response2 = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')->post('https://affiliate.tektravels.com/HotelAPI/Search', [


            "CheckIn" => $validated['checkIn'],
            "CheckOut" => $validated['checkOut'],
            "HotelCodes" => $validated['HotelCode'],
            "GuestNationality" => $validated['guestNationality'],
            "PaxRooms" => [
                [
                    "Adults" => $validated['adults'],
                    "Children" => $validated['children'],
                    "ChildrenAges" => $validated['children'] > 0 ? [null] : null
                ]
            ],
            "ResponseTime" => 23.0,
            "IsDetailedResponse" => true,
            "Filters" => [
                "Refundable" => false,
                "NoOfRooms" => 1,
                "MealType" => 0,
                "OrderBy" => 0,
                "StarRating" => 0,
                "HotelName" => null

            ]
        ]);



        $resp1 = json_decode($response1->getBody()->getContents(), true);
        $resp2 = json_decode($response2->getBody()->getContents(), true);

        $values = ["hoteldetail1" => $resp1['HotelDetails'], "hoteldetail2" => $resp2["HotelResult"]];
        return response()->JSON($values);

    }


    public   function preBooking(Request $request)
              {
        $validated = $request->validate([
            'BookingCode' => 'required',



        ]);


        $response1 = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')->post('https://affiliate.tektravels.com/HotelAPI/PreBook', [
            "BookingCode" => $validated['BookingCode']

        ]);
        $response1 = json_decode($response1->getBody()->getContents(), true);

        return $response1;
    }


  public function bookHotel(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'BookingCode' => 'required|string',
                'IsVoucherBooking' => 'required|boolean',
                'GuestNationality' => 'required|string|size:2',
                'EndUserIp' => 'required|ip',
                'RequestedBookingMode' => 'required|integer|min:1',
                'NetAmount' => 'required|numeric|min:0',
                'HotelRoomsDetails' => 'required|array|min:1',
                'HotelRoomsDetails.*.HotelPassenger' => 'required|array|min:1',
                'HotelRoomsDetails.*.HotelPassenger.*.Title' => 'required|string|in:Mr.,Mrs.,Miss.,Master.',
                'HotelRoomsDetails.*.HotelPassenger.*.FirstName' => 'required|string|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.MiddleName' => 'nullable|string|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.LastName' => 'required|string|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.Email' => 'required|email|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.PaxType' => 'required|integer|in:1,2',
                'HotelRoomsDetails.*.HotelPassenger.*.LeadPassenger' => 'required|boolean',
                'HotelRoomsDetails.*.HotelPassenger.*.Age' => 'required|integer|min:0',
                'HotelRoomsDetails.*.HotelPassenger.*.Phoneno' => 'required|numeric|digits_between:10,15',
                'HotelRoomsDetails.*.HotelPassenger.*.PassportNo' => 'nullable|string|max:50',
                'HotelRoomsDetails.*.HotelPassenger.*.PassportIssueDate' => 'nullable|date',
                'HotelRoomsDetails.*.HotelPassenger.*.PassportExpDate' => 'nullable|date|after:PassportIssueDate',
                'HotelRoomsDetails.*.HotelPassenger.*.PaxId' => 'nullable|integer|min:0',
                'HotelRoomsDetails.*.HotelPassenger.*.PAN' => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
                'HotelRoomsDetails.*.HotelPassenger.*.RoomIndex' => 'nullable|integer|min:1',
                'HotelRoomsDetails.*.HotelPassenger.*.GSTCompanyAddress' => 'nullable|string|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.GSTCompanyContactNumber' => 'nullable|numeric|digits_between:10,15',
                'HotelRoomsDetails.*.HotelPassenger.*.GSTCompanyName' => 'nullable|string|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.GSTNumber' => 'nullable|string|max:50',
                'HotelRoomsDetails.*.HotelPassenger.*.GSTCompanyEmail' => 'nullable|email|max:255',
                'ArrivalTransport' => 'nullable',
                'TraceId' => 'nullable|string|max:255',
            ]);

            // Prepare the payload
            $payload = [
                'BookingCode' => $validated['BookingCode'],
                'IsVoucherBooking' => $validated['IsVoucherBooking'],
                'GuestNationality' => $validated['GuestNationality'],
                'EndUserIp' => $validated['EndUserIp'],
                'RequestedBookingMode' => $validated['RequestedBookingMode'],
                'NetAmount' => $validated['NetAmount'],
                'HotelRoomsDetails' => $validated['HotelRoomsDetails'],
                'IsPackageFare' => $validated['IsPackageFare'] ?? false,
                'IsPackageDetailsMandatory' => $validated['IsPackageDetailsMandatory'] ?? false,
                'ArrivalTransport' => $validated['ArrivalTransport'] ?? null,
                'TraceId' => $validated['TraceId'] ?? null,
            ];

            // Make the HTTP request
            $response = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')
                ->timeout(30)
                ->post('https://HotelBE.tektravels.com/hotelservice.svc/rest/book', $payload);

            // Check if the response is successful
            if ($response->failed()) {
                throw new \Exception('API request failed with status code: ' . $response->status());
            }

            // Decode the response
            $responseData = json_decode($response->body(), true);

            // Check if JSON decoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to parse API response: ' . json_last_error_msg());
            }

            // Check if the API returned an error in the response data
            if (isset($responseData['Error']) || isset($responseData['error'])) {
                $errorMessage = $responseData['Error'] ?? $responseData['error'] ?? 'Unknown API error';
                throw new \Exception('API error: ' . $errorMessage);
            }

            // Prepare booking details for email and PDF
            $bookingDetails = array_merge($payload, $responseData['BookResult']);

            // Generate PDF
            $pdf = PDF::loadView('pdf.booking_confirmationhotel', ['bookingDetails' => $bookingDetails]);
            $pdfPath = storage_path('app/public/booking_confirmation_' . $bookingDetails['BookingId'] . '.pdf');
            $pdf->save($pdfPath);

            // Find lead passenger's email
            $leadPassengerEmail = null;
            foreach ($bookingDetails['HotelRoomsDetails'] as $room) {
                foreach ($room['HotelPassenger'] as $passenger) {
                    if ($passenger['LeadPassenger']) {
                        $leadPassengerEmail = $passenger['Email'];
                        break 2;
                    }
                }
            }

            // Send email with PDF attachment
            if ($leadPassengerEmail) {
                Mail::to($leadPassengerEmail)->send(new HotelBookingConfirmation($bookingDetails, $pdfPath));
                unlink($pdfPath);
            } else {
                \Log::warning('No lead passenger email found for booking ID: ' . $bookingDetails['BookingId']);
            }

            // Return successful response
            return response()->json([
                'status' => 'success',
                'data' => $responseData
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect to the hotel booking service: ' . $e->getMessage()
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the booking: ' . $e->getMessage()
            ], 500);
        }
    }




    public function getBookingDetail(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'BookingId' => 'required|string',
            'EndUserIp' => 'required|ip',
            'TokenId' => 'required|string'
        ]);

        // Send request to GetBookingDetail API
        $response = Http::post('http://HotelBE.tektravels.com/internalhotelservice.svc/rest/GetBookingDetail', [
            "BookingId" => $validated['BookingId'],
            "EndUserIp" => $validated['EndUserIp'],
            "TokenId" => $validated['TokenId']
        ]);

        // Decode API response
        $responseData = json_decode($response->body(), true);

        return response()->json($responseData);
    }

}