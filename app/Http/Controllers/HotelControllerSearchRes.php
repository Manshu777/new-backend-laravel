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
use GuzzleHttp\Pool;
use App\Services\ApiService;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Exception\RequestException;
use App\Models\Bookedhotels;
use Illuminate\Support\Facades\Log;
class HotelControllerSearchRes extends Controller
{

    protected $apiService;
     public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    public function getCountries(Request $request)
 {
            // API endpoint
            $apiUrl = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CountryList';

            $username = 'TBOStaticAPITest';
            $password = 'Tbo@11530818'; 

            try {
                // Make the API request using Laravel's HTTP client
                $response = Http::withBasicAuth($username, $password)
                    ->get($apiUrl);

                // Check if the request was successful
                if ($response->successful()) {
                    $data = $response->json();

                    $countries = isset($data['CountryList']) ? $data['CountryList'] : [];

                    // Corrected mapping here:
                    $formattedCountries = array_filter(array_map(function ($country) {
                        if (!isset($country['Code'], $country['Name'])) {
                            return null;
                        }
                        return [
                            'Code' => $country['Code'],
                            'Name' => $country['Name']
                        ];
                    }, $countries));

                    return response()->json([
                        'status' => 'success',
                        'data' => $formattedCountries
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to fetch countries from TBO API'
                    ], 500);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
        }





    public function searchHotels(Request $request)
    {
        $validated = $request->validate([
            'cityCode' => 'required|string',
            'checkIn' => 'required|date',
            'checkOut' => 'required|date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'childrenAges' => 'nullable|array',
            'childrenAges.*' => 'nullable|integer|min:0|max:18',
            'guestNationality' => 'required|string',
        ]);



       

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
            $executionTime = (microtime(true) - $startTime) * 1000; 
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

        $hotelCodes = array_slice($hotelCodes, 0, 1000); 
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
                        "NoOfRooms" => 0,
                        "MealType" => 0,
                        "OrderBy" => 0,
                        "StarRating" => 0,
                        "HotelName" => null,
                    ]
                ];

                Log::info('Hotel Search API Request', [
                    'payload' => $payload,
                ]);

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
             

                // Check if the response indicates no available rooms globally
                if (isset($searchResults['Status']['Code']) && $searchResults['Status']['Code'] === 201 && $searchResults['Status']['Description'] === "No Available rooms for given criteria") {
                    return;
                }

                // Process search results for each hotel
                $hotels = $searchResults['HotelResult'] ?? $searchResults['HotelResults'] ?? [];
                foreach ($hotels as $hotelResult) {
                    $hotelCode = $hotelResult['HotelCode'] ?? null;
                    if (!$hotelCode) {
                       
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

    try {
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
                    "ChildrenAges" => $validated['children'] > 0 ? ($validated['childrenAges'] ?? array_fill(0, $validated['children'], null)) : null,
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

        // Debug: Log the full response to inspect its structure
        \Log::info('API 2 Response', $resp2);

        // Check if HotelResult exists
        if (!isset($resp2['HotelResult'])) {
            return response()->json([
                'error' => 'HotelResult key missing in API response',
                'response' => $resp2
            ], 500);
        }

        $values = [
            "hoteldetail1" => $resp1['HotelDetails'],
            "hoteldetail2" => $resp2["HotelResult"]
        ];

        return response()->json($values);
    } catch (\Exception $e) {
        \Log::error('Error in singleHotelget: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while fetching hotel details'], 500);
    }
}


    public  function preBooking(Request $request)
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
        $token = $this->apiService->getToken();
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'BookingCode' => 'required|string',
                'IsVoucherBooking' => 'required|boolean',
                'GuestNationality' => 'required|string|size:2',
                'EndUserIp' => 'required|ip',
                'RequestedBookingType' => 'required|integer|min:1',
                'NetAmount' => 'required|numeric|min:0',
                'HotelRoomsDetails' => 'required|array|min:1',
                'HotelRoomsDetails.*.HotelPassenger' => 'required|array|min:1',
                'HotelRoomsDetails.*.HotelPassenger.*.Title' => 'required|string',
                'HotelRoomsDetails.*.HotelPassenger.*.FirstName' => 'required|string|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.MiddleName' => 'nullable|string|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.LastName' => 'required|string|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.Email' => 'required|email|max:255',
                'HotelRoomsDetails.*.HotelPassenger.*.PaxType' => 'required|integer|in:1,2',
                'HotelRoomsDetails.*.HotelPassenger.*.LeadPassenger' => 'required|boolean',
                'HotelRoomsDetails.*.HotelPassenger.*.Age' => 'integer',
                'HotelRoomsDetails.*.HotelPassenger.*.Phoneno' => 'nullable',
                'HotelRoomsDetails.*.HotelPassenger.*.PassportNo' => 'nullable|string|max:50',
                'HotelRoomsDetails.*.HotelPassenger.*.PassportIssueDate' => 'nullable',
                'HotelRoomsDetails.*.HotelPassenger.*.PassportExpDate' => 'nullable',
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
                'TokenId' => 'nullable|string|max:255',
            ]);

            // Prepare the payload
            $payload = [
                'BookingCode' => $validated['BookingCode'],
                'IsVoucherBooking' => $validated['IsVoucherBooking'],
                'GuestNationality' => $validated['GuestNationality'],
                'EndUserIp' => $validated['EndUserIp'],
                'RequestedBookingType' => $validated['RequestedBookingType'],
                'NetAmount' => $validated['NetAmount'],
                'HotelRoomsDetails' => $validated['HotelRoomsDetails'],
                'IsPackageFare' => $validated['IsPackageFare'] ?? false,
                'IsPackageDetailsMandatory' => $validated['IsPackageDetailsMandatory'] ?? false,
                'ArrivalTransport' => $validated['ArrivalTransport'] ?? null,
                'TraceId' => $validated['TraceId'] ?? null,
            ];

            // Make the HTTP request for booking
            Log::info('Hotel Booking Payload:', $payload);

            $response = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')
                ->timeout(30)
                ->post('https://HotelBE.tektravels.com/hotelservice.svc/rest/book', $payload);

            // Check if the response is successful
            if ($response->failed()) {
                throw new Exception('API request failed with status code: ' . $response->status());
            }

            // Decode the response
            $responseData = json_decode($response->body(), true);

            // Check if JSON decoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Failed to parse API response: ' . json_last_error_msg());
            }

            // Check if the API returned an error in the response data
            if (isset($responseData['Error']) || isset($responseData['error'])) {
                $errorMessage = $responseData['Error'] ?? $responseData['error'] ?? 'Unknown API error';
                throw new Exception('API error: ' . $errorMessage);
            }

            // Extract booking details
            $bookResult = $responseData['BookResult'] ?? [];
            $leadPassenger = null;
            foreach ($validated['HotelRoomsDetails'] as $room) {
                foreach ($room['HotelPassenger'] as $passenger) {
                    if ($passenger['LeadPassenger']) {
                        $leadPassenger = $passenger;
                        break 2;
                    }
                }
            }

            // Save initial booking data to Bookedhotels table
            $bookedHotel = Bookedhotels::create([
                'user_id' => '1', // Assuming authenticated user
                'enduserip' => $validated['EndUserIp'],
                'hotel_id' => $bookResult['HotelId'] ?? null,
                'user_name' => $leadPassenger ? ($leadPassenger['Title'] . ' ' . $leadPassenger['FirstName'] . ' ' . $leadPassenger['LastName']) : null,
                'user_number' => $leadPassenger['Phoneno'] ?? null,
                'hotel_name' => $bookResult['HotelName'] ?? null,
                'location' => $bookResult['Location'] ?? null,
                'address' => $bookResult['Address'] ?? null,
                'check_in_date' => $bookResult['CheckInDate'] ?? '2025-09-22',
                'check_out_date' => $bookResult['CheckOutDate'] ?? '2025-09-22',
                'room_type' => $bookResult['RoomType'] ?? 'rfr',
                'price' => $validated['NetAmount'],
                'date_of_booking' => now(),
                'initial_response' => json_encode($responseData),
                'refund' => false,
                'response' => json_encode($bookResult),
                'tokenid' => $token,
                'traceid' => $bookResult['TraceId'] ?? null,
                'booking_id' => $bookResult['BookingId'] ?? null,
                'pnr' => $bookResult['ConfirmationNo'] ?? null,
            ]);

            // Fetch booking details from GetBookingDetail API
            $bookingId = $bookResult['BookingId'] ?? null;
            if ($bookingId) {
                $bookingDetailPayload = [
                    'BookingId' => $bookingId,
                    'EndUserIp' => $validated['EndUserIp'],
                ];

                Log::info('GetBookingDetail Payload:', $bookingDetailPayload);

                $bookingDetailResponse = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')
                    ->timeout(30)
                    ->post('https://HotelBE.tektravels.com/hotelservice.svc/rest/GetBookingDetail', $bookingDetailPayload);

                if ($bookingDetailResponse->successful()) {
                    $bookingDetailData = json_decode($bookingDetailResponse->body(), true);

                    if (json_last_error() === JSON_ERROR_NONE && isset($bookingDetailData['status']) && $bookingDetailData['status'] === 'success') {
                        $bookingData = $bookingDetailData['data'] ?? [];

                        // Extract specific fields to save
                        $bookingDetailsToSave = [
                            'booking_id' => $bookingData['BookingId'] ?? $bookingId,
                            'enduserip' => $validated['EndUserIp'],
                            'hotel_name' => $bookingData['HotelName'] ?? null,
                            'hotel_booking_status' => $bookingData['HotelBookingStatus'] ?? null,
                            'confirmation_no' => $bookingData['HotelConfirmationNo'] ?? null,
                            'check_in_date' => $bookingData['CheckInDate'] ?? null,
                            'check_out_date' => $bookingData['CheckOutDate'] ?? null,
                            'net_amount' => $bookingData['NetAmount'] ?? null,
                            'last_cancellation_date' => $bookingData['LastCancellationDate'] ?? null,
                            'star_rating' => $bookingData['StarRating'] ?? null,
                            'address_line1' => $bookingData['AddressLine1'] ?? null,
                            'city' => $bookingData['City'] ?? null,
                            'country_code' => $bookingData['CountryCode'] ?? null,
                            'no_of_rooms' => $bookingData['NoOfRooms'] ?? null,
                            'booking_date' => $bookingData['BookingDate'] ?? null,
                        ];

                        // Update the existing booked hotel record with specific details
                        $bookedHotel->update($bookingDetailsToSave);

                        Log::info('Specific booking details saved successfully for BookingId: ' . $bookingId);
                    } else {
                        Log::warning('Failed to parse GetBookingDetail response or response status not success for BookingId: ' . $bookingId);
                    }
                } else {
                    Log::error('GetBookingDetail API request failed with status code: ' . $bookingDetailResponse->status());
                }
            } else {
                Log::warning('No BookingId found to fetch booking details');
            }

            // Prepare booking details for email and PDF
            $bookingDetails = array_merge($payload, $bookResult);

            // Generate PDF
            $pdf = PDF::loadView('pdf.booking_confirmationhotel', ['bookingDetails' => $bookingDetails]);
            $pdfPath = storage_path('app/public/booking_confirmation_' . ($bookingDetails['BookingId'] ?? 'unknown') . '.pdf');
            $pdf->save($pdfPath);

            // Send email with PDF attachment
            if ($leadPassenger && $leadPassenger['Email']) {
                Mail::to($leadPassenger['Email'])->send(new HotelBookingConfirmation($bookingDetails, $pdfPath));
                unlink($pdfPath);
            } else {
                Log::warning('No lead passenger email found for booking ID: ' . ($bookingDetails['BookingId'] ?? 'unknown'));
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
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the booking: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getBookingDetails(Request $request)
    {
        try {
            $token = $this->apiService->getToken(); // Ensure this fetches the correct token for the hotel API
    
            // Define validation rules (simplified for hotel API, assuming only BookingId and EndUserIp are needed)
            $rules = [
                'EndUserIp' => 'required|string|ip',
                'BookingId' => 'required|integer|min:1',
            ];
    
            // Create validator
            $validator = Validator::make($request->all(), $rules, [], [
                'EndUserIp' => 'End User IP',
                'BookingId' => 'Booking ID',
            ]);
    
            // Return validation errors if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            // Prepare the payload
            $validatedData = $validator->validated();
            $payload = [
                'EndUserIp' => $validatedData['EndUserIp'],
     
                'BookingId' => $validatedData['BookingId'],
            ];
    
            // Make the API request to the hotel API
            $response = Http::timeout(100)->post(
                'https://HotelBE.tektravels.com/hotelservice.svc/rest/GetBookingDetail',
                $payload
            );
    
            // Handle API errors
            if ($response->failed()) {
                throw new \Exception('Initial API request failed: ' . $response->body());
            }
    
            // Handle token expiration (adjust error code based on hotel API documentation)
            if ($response->json('GetBookingDetailResult.Error.ErrorCode') === 6) { // Update error code if different
                $token = $this->apiService->authenticate(); // Refresh token
                $payload['TokenId'] = $token;
                $response = Http::timeout(90)->post(
                    'https://HotelBE.tektravels.com/hotelservice.svc/rest/GetBookingDetail',
                    $payload
                );
    
                if ($response->failed()) {
                    throw new \Exception('Retry API request failed after token refresh: ' . $response->body());
                }
            }
    
            // Check response status
            if ($response->json('GetBookingDetailResult.ResponseStatus') !== 1) {
                $errorMessage = $response->json('GetBookingDetailResult.Error.ErrorMessage') ?? 'Unknown error';
                throw new \Exception('Failed to fetch booking details: ' . $errorMessage);
            }
    
            $bookingResponse = $response->json('GetBookingDetailResult');
    
            return response()->json([
                'status' => 'success',
                'data' => $bookingResponse,
                'message' => 'Booking details fetched successfully',
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Booking Details API Request Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'API request timeout or connection error',
                'error' => $e->getMessage(),
            ], 503);
        } catch (\Exception $e) {
            Log::error('Booking Details Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching booking details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
     
    
    
    
    
      
    public function getBookingDetailHotel(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'EndUserIp' => 'required|string|ip',
                'BookingId' => 'required|integer|min:1',
            ];
    
            // Create validator
            $validator = Validator::make($request->all(), $rules, [], [
                'EndUserIp' => 'End User IP',
                'BookingId' => 'Booking ID',
            ]);
    
            // Return validation errors if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            // Prepare the payload
            $validatedData = $validator->validated();
            $payload = [
                'EndUserIp' => $validatedData['EndUserIp'],
                'BookingId' => $validatedData['BookingId'],
            ];
    
            // Log the payload for debugging
            Log::info('GetBookingDetail Payload Input', ['data' => $payload]);
    
            // Prepare Basic Auth credentials
            $username = 'Apkatrip';
            $password = 'Apkatrip@1234';
            $credentials = base64_encode("{$username}:{$password}");
    
            // Make the API request with Basic Auth
            $response = Http::withHeaders([
                'Authorization' => "Basic {$credentials}",
                'Content-Type' => 'application/json',
            ])->timeout(100)->post(
                'https://HotelBE.tektravels.com/hotelservice.svc/rest/GetBookingDetail',
                $payload
            );
    
            // Log the raw response for debugging
            Log::info('GetBookingDetail Raw Response', ['body' => $response->body()]);
    
            // Handle API errors
            if ($response->failed()) {
                Log::error('GetBookingDetail API Request Failed', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
                throw new \Exception('API request failed: ' . $response->body());
            }
    
            // Parse the JSON response
            $responseData = $response->json();
    
            // Check if response is empty or missing GetBookingDetailResult
            if (!$responseData || !isset($responseData['GetBookingDetailResult'])) {
                Log::warning('GetBookingDetail Invalid Response', [
                    'data' => $responseData,
                    'expected' => 'GetBookingDetailResult',
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or empty response from the API.',
                ], 500);
            }
    
            $bookingResult = $responseData['GetBookingDetailResult'];
    
            // Check response status
            if ($bookingResult['ResponseStatus'] !== 1) {
                $errorMessage = $bookingResult['Error']['ErrorMessage'] ?? 'Unknown error';
                Log::error('GetBookingDetail Failed', [
                    'error' => $errorMessage,
                    'response' => $bookingResult,
                ]);
                throw new \Exception('Failed to fetch booking details: ' . $errorMessage);
            }
    
            // Return successful response
            return response()->json([
                'status' => 'success',
                'data' => $bookingResult,
                'message' => 'Booking details fetched successfully.',
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('GetBookingDetail API Request Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'API request timeout or connection error.',
                'error' => $e->getMessage(),
            ], 503);
        } catch (\Exception $e) {
            Log::error('GetBookingDetail Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching booking details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
        
    
    
    public function cancelHotelBooking(Request $request)
    {
            // Validate input parameters
            $validated = $request->validate([
                'BookingMode' => 'required|integer',
                'ChangeRequestId' => 'required|integer',
                'EndUserIp' => 'required|ip',
                'BookingId' => 'required|integer',
                'RequestType' => 'required|in:4',
                'Remarks' => 'required|string',
            ]);
        
            try {
                // Retrieve the booking from the database using BookingId
                $booking = Bookedhotels::where('bookingId', $validated['BookingId'])->first();
        
                if (!$booking || !$booking->tokenid) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No booking or TokenId found for the provided BookingId.',
                        'error' => [
                            'ErrorCode' => 404,
                            'ErrorMessage' => 'Booking or TokenId not found'
                        ]
                    ], 404);
                }
        
                // Prepare payload for API using TokenId from database
                $payload = [
                    'BookingMode' => $validated['BookingMode'],
                    'ChangeRequestId' => $validated['ChangeRequestId'],
                    'EndUserIp' => $validated['EndUserIp'],
                    'TokenId' => $booking->tokenid,
                    'BookingId' => $validated['BookingId'],
                    'RequestType' => $validated['RequestType'],
                    'Remarks' => $validated['Remarks'],
                ];
        
                // Call the CancelBooking API using environment variables for credentials
                $response = Http::withBasicAuth(
                    config('services.hotel_api.username', 'Apkatrip'),
                    config('services.hotel_api.password', 'Apkatrip@1234')
                )->post(
                    config('services.hotel_api.cancel_endpoint', 'http://HotelBE.tektravels.com/internalhotelservice.svc/rest/SendChangeRequest'),
                    $payload
                );
        
                // Check if response is valid JSON
                $responseData = json_decode($response->body(), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Invalid JSON response from hotel cancellation API', [
                        'body' => $response->body(),
                        'status' => $response->status(),
                    ]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid response from cancellation API.',
                        'error' => [
                            'ErrorCode' => -1,
                            'ErrorMessage' => 'Invalid JSON response'
                        ]
                    ], 500);
                }
        
                // Log the API request and response for debugging
                Log::info('Hotel cancellation API call', [
                    'request' => $payload,
                    'response' => $responseData,
                    'status' => $response->status(),
                ]);
        
                // Check if API call was successful
                if ($response->failed() || isset($responseData['Error']['ErrorCode']) && $responseData['Error']['ErrorCode'] != 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to process cancellation request.',
                        'error' => $responseData['Error'] ?? [
                            'ErrorCode' => -1,
                            'ErrorMessage' => 'API request failed'
                        ]
                    ], 500);
                }
        
                // Update Bookedhotels table on successful cancellation
                $booking->update([
                    'response' => json_encode($responseData), // Store API response
                    'refund' => isset($responseData['RefundAmount']) && $responseData['RefundAmount'] > 0 ? 1 : 0, // Set refund based on API response
                    'updated_at' => now(), // Ensure timestamp is updated
                ]);
        
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'data' => $responseData,
                ], 200);
        
            } catch (\Exception $e) {
                // Log unexpected errors
                Log::error('Unexpected error in cancelHotelBooking', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                return response()->json([
                    'status' => 'error',
                    'message' => 'An unexpected error occurred.',
                    'error' => [
                        'ErrorCode' => -1,
                        'ErrorMessage' => 'Internal server error'
                    ]
                ], 500);
            }
    }
    
 




  


}