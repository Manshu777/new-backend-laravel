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
use Illuminate\Support\Facades\Validator;
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
            $apiUrl = 'https://apiwr.tboholidays.com/HotelAPI/CountryList';

            $username = 'travelcategory';
            $password = 'Tra@59334536'; 

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
            $response1 = $client->post('https://apiwr.tboholidays.com/HotelAPI/TBOHotelCodeList', [
                'auth' => ['travelcategory', 'Tra@59334536'],
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

                yield new GuzzleRequest('POST', 'https://affiliate.travelboutiqueonline.com/HotelAPI/Search', [
                    'Authorization' => 'Basic ' . base64_encode('IXCN483:#New@api48#'),
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
                        $response2 = $client->post('https://apiwr.tboholidays.com/HotelAPI/Hoteldetails', [
                            'auth' => ['travelcategory', 'Tra@59334536'],
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
        $response1 = Http::withBasicAuth('travelcategory', 'Tra@59334536')->post('https://apiwr.tboholidays.com/HotelAPI/Hoteldetails', [
            "Hotelcodes" => $validated['HotelCode'],
            "Language" => "EN"
        ]);

        $response2 = Http::withBasicAuth('IXCN483', '#New@api48#')->post('https://affiliate.travelboutiqueonline.com/HotelAPI/Search', [
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


        $response1 = Http::withBasicAuth('IXCN483', '#New@api48#')->post('ttps://affiliate.travelboutiqueonline.com/HotelAPI/PreBook', [
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
                'RequestedBookingMode' => 'required|integer|min:1',
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
                'TokenId' => 'nullable|string|max:255', // Added TokenId validation
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
            Log::info('Hotel Booking Payload:', $payload);

            $response = Http::withBasicAuth('IXCN483', '#New@api48#')
                ->timeout(60)
                ->post('https://hotelbooking.travelboutiqueonline.com/HotelAPI_V10/HotelService.svc/rest/book', $payload);

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
            $bookingDetails = array_merge($payload, $bookResult);

            // Save booking data to Bookedhotels table
            $bookedHotel = Bookedhotels::create([
                'user_id' => '1',// Assuming authenticated user
                'enduserip' => $validated['EndUserIp'],
                'hotel_id' => $bookResult['HotelId'] ?? null, // Adjust based on actual response structure
                'user_name' => $leadPassenger ? ($leadPassenger['Title'] . ' ' . $leadPassenger['FirstName'] . ' ' . $leadPassenger['LastName']) : null,
                'user_number' => $leadPassenger['Phoneno'] ?? null,
                'hotel_name' => $bookResult['HotelName'] ?? null, // Adjust based on actual response structure
                'location' => $bookResult['Location'] ?? null, // Adjust based on actual response structure
                'address' => $bookResult['Address'] ?? null, // Adjust based on actual response structure
                'check_in_date' => $bookResult['CheckInDate'] ?? '2025-09-22', // Adjust based on actual response structure
                'check_out_date' => $bookResult['CheckOutDate'] ?? '2025-09-22', // Adjust based on actual response structure
                'room_type' => $bookResult['RoomType'] ?? 'rfr', // Adjust based on actual response structure
                'price' => $validated['NetAmount'],
                'date_of_booking' => now(),
                'initial_response' => json_encode($responseData),
                'refund' => false,
                'response' => json_encode($bookResult),
                'tokenid' =>  $token,
                'traceid' => $bookResult['TraceId'] ?? null,
                'bookingId' => $bookResult['BookingId'] ?? null, // Adjust based on actual response structure
                'pnr' => $bookResult['ConfirmationNo'] ?? null, // Adjust based on actual response structure
            ]);

            // Prepare booking details for email and PDF
          

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
        } catch (\Exception $e) {
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
                'https://hotelbooking.travelboutiqueonline.com/HotelAPI_V10/HotelService.svc/rest/GetBookingDetail',
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
                    'https://hotelbooking.travelboutiqueonline.com/HotelAPI_V10/HotelService.svc/rest/GetBookingDetail',
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
            $username = 'IXCN483';
            $password = '#New@api48#';
            $credentials = base64_encode("{$username}:{$password}");
    
            // Make the API request with Basic Auth
            $response = Http::withHeaders([
                'Authorization' => "Basic {$credentials}",
                'Content-Type' => 'application/json',
            ])->timeout(100)->post(
                'https://hotelbooking.travelboutiqueonline.com/HotelAPI_V10/HotelService.svc/rest/GetBookingDetail',
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
            'RequestType' => 'required|in:4', // Assuming 4 is for cancellation
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
    
            // Check if booking is already cancelled or failed
            $responseData = json_decode($booking->response, true);
            if ($booking->cancellation_status === 'Cancelled' || (isset($responseData['HotelBookingStatus']) && $responseData['HotelBookingStatus'] === 'Cancelled')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking is already cancelled.',
                    'error' => [
                        'ErrorCode' => 400,
                        'ErrorMessage' => 'Booking already cancelled'
                    ]
                ], 400);
            }
    
            if (isset($responseData['ResponseStatus']) && $responseData['ResponseStatus'] == 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking cannot be cancelled due to existing cancellation restrictions.',
                    'error' => [
                        'ErrorCode' => 400,
                        'ErrorMessage' => $responseData['Error']['ErrorMessage'] ?? 'Cancellation restricted'
                    ]
                ], 400);
            }
    
            // Prepare payload for API
            $payload = [
                'BookingMode' => $validated['BookingMode'],
                'ChangeRequestId' => $validated['ChangeRequestId'],
                'EndUserIp' => $validated['EndUserIp'],
                'TokenId' => $booking->tokenid,
                'BookingId' => $validated['BookingId'],
                'RequestType' => $validated['RequestType'],
                'Remarks' => $validated['Remarks'],
                'ConfirmationNo' => $booking->confirmation_no,
                'PNR' => $booking->pnr,
            ];
    
            // Update booking with cancellation request details
            $booking->update([
                'cancellation_status' => 'Requested',
                'cancellation_remarks' => $validated['Remarks'],
                'cancellation_request_id' => $validated['ChangeRequestId'],
                'cancellation_initiated_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Call the CancelBooking API
            $response = Http::withBasicAuth(
                config('services.hotel_api.username', 'IXCN483'),
                config('services.hotel_api.password', '#New@api48#')
            )->post(
                config('services.hotel_api.cancel_endpoint', 'https://hotelbooking.travelboutiqueonline.com/HotelAPI_V10/HotelService.svc/rest/SendChangeRequest'),
                $payload
            );
    
            // Check if response is valid JSON
            $responseData = json_decode($response->body(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON response from hotel cancellation API', [
                    'body' => $response->body(),
                    'status' => $response->status(),
                ]);
                $booking->update(['cancellation_status' => 'Failed']);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid response from cancellation API.',
                    'error' => [
                        'ErrorCode' => -1,
                        'ErrorMessage' => 'Invalid JSON response'
                    ]
                ], 500);
            }
    
            // Log the API request and response
            Log::info('Hotel cancellation API call', [
                'request' => $payload,
                'response' => $responseData,
                'status' => $response->status(),
            ]);
    
            // Check if API call was successful
            if ($response->failed() || (isset($responseData['Error']['ErrorCode']) && $responseData['Error']['ErrorCode'] != 0)) {
                $booking->update(['cancellation_status' => 'Failed']);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to process cancellation request.',
                    'error' => $responseData['Error'] ?? [
                        'ErrorCode' => -1,
                        'ErrorMessage' => 'API request failed'
                    ]
                ], 500);
            }
    
            // Determine cancellation status from API response
            $cancellationStatus = isset($responseData['HotelChangeRequestResult']['ChangeRequestStatus']) && $responseData['HotelChangeRequestResult']['ChangeRequestStatus'] == 1
                ? 'Cancelled'
                : 'Pending';
    
            // Update Bookedhotels table
            $booking->update([
                'response' => json_encode($responseData),
                'hotel_booking_status' => $cancellationStatus === 'Cancelled' ? 'Cancelled' : $booking->hotel_booking_status,
                'cancellation_status' => $cancellationStatus,
                'refund' => isset($responseData['RefundAmount']) && $responseData['RefundAmount'] > 0 ? 1 : 0,
                'refund_amount' => $responseData['RefundAmount'] ?? null,
                'net_amount' => $responseData['RefundAmount'] ?? $booking->net_amount,
                'last_cancellation_date' => $cancellationStatus === 'Cancelled' ? now()->toDateString() : $booking->last_cancellation_date,
                'updated_at' => now(),
            ]);
    
            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Cancellation request processed successfully.',
                'data' => [
                    'bookingId' => $booking->bookingId,
                    'hotel_booking_status' => $booking->hotel_booking_status,
                    'cancellation_status' => $booking->cancellation_status,
                    'refund' => $booking->refund,
                    'refund_amount' => $booking->refund_amount,
                    'confirmation_no' => $booking->confirmation_no,
                    'api_response' => $responseData,
                ],
            ], 200);
    
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Unexpected error in cancelHotelBooking', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            // Update cancellation status to Failed on error
            if ($booking) {
                $booking->update(['cancellation_status' => 'Failed']);
            }
    
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred during cancellation.',
                'error' => [
                    'ErrorCode' => -1,
                    'ErrorMessage' => 'Internal server error'
                ]
            ], 500);
        }
    }




  


}