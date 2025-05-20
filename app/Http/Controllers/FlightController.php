<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ApiService;
use Carbon\Carbon;
use App\Models\Bookflights;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\RateLimiter;
use App\Mail\BookingConfirmation;
use Illuminate\Support\Facades\Mail;
use App\Models\RazorpayOrder;


class FlightController extends Controller
{
    protected $apiService;
     public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    public function searchFlights(Request $request)
    {
        $token = $this->apiService->getToken();
    
        $validatedData = $request->validate([
            "EndUserIp" => 'required',
            'AdultCount' => 'required|integer',
            'Origin' => 'required|string',
            'Destination' => 'required|string',
            'FlightCabinClass' => 'required|integer',
            'PreferredDepartureTime' => 'required',
            'ChildCount' => 'nullable|integer',
            'InfantCount' => 'nullable|integer',
            'DirectFlight' => 'nullable|boolean',
            'OneStopFlight' => 'nullable|boolean',
            'JourneyType' => 'required|integer',
            'PreferredAirlines' => 'nullable|string',
        ]);
    
        // Prepare the search payload
        $searchPayload = [
            "EndUserIp" => $validatedData['EndUserIp'],
            "TokenId" => $token,
            "AdultCount" => $validatedData['AdultCount'],
            "ChildCount" => $validatedData['ChildCount'],
            "InfantCount" => $validatedData['InfantCount'],
            "DirectFlight" => $validatedData['DirectFlight'],
            "OneStopFlight" => $validatedData['OneStopFlight'],
            "JourneyType" => $validatedData['JourneyType'],
            "PreferredAirlines" => $validatedData['PreferredAirlines'],
            "Segments" => [
                [
                    "Origin" => $validatedData['Origin'],
                    "Destination" => $validatedData['Destination'],
                    "FlightCabinClass" => $validatedData['FlightCabinClass'],
                    "PreferredDepartureTime" => $validatedData['PreferredDepartureTime'],
                    "PreferredArrivalTime" => $validatedData['PreferredDepartureTime'],
                ],
            ],
            "Sources" => null,
        ];
    
        // Send API Request
        $response = Http::timeout(100)->withHeaders([])->post(
            'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Search',
            $searchPayload
        );
    
        if ($response->json('Response.Error.ErrorCode') === 6) {
            $token = $this->apiService->authenticate();
            $response = Http::timeout(90)->withHeaders([])->post(
                'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Search',
                $searchPayload
            );
        }
    
        $results = $response->json();
        $newBaseFare =0;
        if (!empty($results['Results'])) {
            $newBaseFare =3;
            foreach ($results['Results'] as &$resultGroup) {
                foreach ($resultGroup as &$result) {
                    $baseFare = $result['Fare']['BaseFare'];
                    $newBaseFare = $baseFare * 1.1;
                    $result['Fare']['BaseFare'] = round($newBaseFare, 2);
    
                    // Optionally, recalculate PublishedFare or other fields
                    $result['Fare']['PublishedFare'] = round($newBaseFare + $result['Fare']['Tax'], 2);
                }
            }
        }
    
        return $results;
    }


    public function getUserBookings(string $id)
    {
        try {
            // Fetch bookings for the specified user ID
            $bookings = Bookflights::where('user_id', $id)->get();

            if ($bookings->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'data' => [],
                    'message' => 'No bookings found for this user.',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => $bookings,
                'message' => 'Bookings retrieved successfully.',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('GetUserBookings Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving bookings.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    private function isWeekend($date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        return $dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY;
    }



    public function bookFlight(Request $request)
    {
        $token = $this->apiService->getToken();
    
        // Validate the request data, including Fare
        $validatedData = $request->validate([
            'ResultIndex' => 'required|string',
            'Passengers' => 'required|array',
            'Passengers.*.Title' => 'required|string',
            'Passengers.*.FirstName' => 'required|string',
            'Passengers.*.LastName' => 'required|string',
            'Passengers.*.PaxType' => 'required|integer',
            'Passengers.*.DateOfBirth' => 'required|date',
            'Passengers.*.Gender' => 'required|integer',
            'Passengers.*.PassportNo' => 'required|string',
            'Passengers.*.PassportExpiry' => 'required|date',
            'Passengers.*.AddressLine1' => 'required|string',
            'Passengers.*.City' => 'required|string',
            'Passengers.*.CountryCode' => 'required|string',
            'Passengers.*.ContactNo' => 'required|string',
            'Passengers.*.Email' => 'required|email',
            'Passengers.*.IsLeadPax' => 'required|boolean',
            'Passengers.*.Fare' => 'required|array',
            'Passengers.*.Fare.Currency' => 'required|string',
            'Passengers.*.Fare.BaseFare' => 'required|numeric',
            'Passengers.*.Fare.Tax' => 'required|numeric',
            'Passengers.*.Fare.YQTax' => 'nullable|numeric',
            'Passengers.*.Fare.AdditionalTxnFeePub' => 'nullable|numeric',
            'Passengers.*.Fare.AdditionalTxnFeeOfrd' => 'nullable|numeric',
            'Passengers.*.Fare.OtherCharges' => 'nullable|numeric',
            'Passengers.*.Fare.Discount' => 'nullable|numeric',
            'Passengers.*.Fare.PublishedFare' => 'required|numeric',
            'Passengers.*.Fare.OfferedFare' => 'required|numeric',
            'Passengers.*.Fare.TdsOnCommission' => 'nullable|numeric',
            'Passengers.*.Fare.TdsOnPLB' => 'nullable|numeric',
            'Passengers.*.Fare.TdsOnIncentive' => 'nullable|numeric',
            'Passengers.*.Fare.ServiceFee' => 'nullable|numeric',
            'EndUserIp' => 'required|string',
            'TraceId' => 'required|string',
        ]);
    
        // Prepare the booking payload
        $bookingPayload = [
            "ResultIndex" => $validatedData['ResultIndex'],
            "Passengers" => [],
            "EndUserIp" => $validatedData['EndUserIp'],
            "TokenId" => $token,
            "TraceId" => $validatedData['TraceId'],
        ];
    
        // Loop through each passenger and add their details, including Fare
        foreach ($validatedData['Passengers'] as $passenger) {
            $bookingPayload['Passengers'][] = [
                "Title" => $passenger['Title'],
                "FirstName" => $passenger['FirstName'],
                "LastName" => $passenger['LastName'],
                "PaxType" => $passenger['PaxType'],
                "DateOfBirth" => $passenger['DateOfBirth'],
                "Gender" => $passenger['Gender'],
                "PassportNo" => $passenger['PassportNo'],
                "PassportExpiry" => $passenger['PassportExpiry'],
                "AddressLine1" => $passenger['AddressLine1'],
                "City" => $passenger['City'],
                "CountryCode" => $passenger['CountryCode'],
                "ContactNo" => $passenger['ContactNo'],
                "Email" => $passenger['Email'],
                "IsLeadPax" => $passenger['IsLeadPax'],
                "Fare" => [
                    "Currency" => $passenger['Fare']['Currency'],
                    "BaseFare" => $passenger['Fare']['BaseFare'],
                    "Tax" => $passenger['Fare']['Tax'],
                    "YQTax" => $passenger['Fare']['YQTax'] ?? 0,
                    "AdditionalTxnFeePub" => $passenger['Fare']['AdditionalTxnFeePub'] ?? 0.0,
                    "AdditionalTxnFeeOfrd" => $passenger['Fare']['AdditionalTxnFeeOfrd'] ?? 0.0,
                    "OtherCharges" => $passenger['Fare']['OtherCharges'] ?? 0.0,
                    "Discount" => $passenger['Fare']['Discount'] ?? 0.0,
                    "PublishedFare" => $passenger['Fare']['PublishedFare'],
                    "OfferedFare" => $passenger['Fare']['OfferedFare'],
                    "TdsOnCommission" => $passenger['Fare']['TdsOnCommission'] ?? 0,
                    "TdsOnPLB" => $passenger['Fare']['TdsOnPLB'] ?? 0,
                    "TdsOnIncentive" => $passenger['Fare']['TdsOnIncentive'] ?? 0,
                    "ServiceFee" => $passenger['Fare']['ServiceFee'] ?? 0,
                ],
            ];
        }
    
        // Make the API request
        $response = Http::timeout(100)->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Book', $bookingPayload);
    
        // Handle token expiration
        if ($response->json('Response.Error.ErrorCode') === 6) {
            $token = $this->apiService->authenticate();
            $bookingPayload['TokenId'] = $token;
            $response = Http::timeout(90)->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Book', $bookingPayload);
        }
    
        // Check if the booking was successful
        if ($response->json('Response.ResponseStatus') === 1) {
            $bookingResponse = $response->json('Response.Response');
    
           
            FlightBooking::create([
                'token' => $token,
                'trace_id' => $validatedData['TraceId'],
                'user_ip' => $validatedData['EndUserIp'],
                'pnr' => $bookingResponse['PNR'],
                'booking_id' => $bookingResponse['BookingId'],
     
                'username' => $validatedData['email'],
                'user_name' => $validatedData['Passengers'][0]['FirstName'] . ' ' . $validatedData['Passengers'][0]['LastName'],
                'phone_number' => $validatedData['Passengers'][0]['ContactNo'],
            ]);


            $ticket = [
                'pnr' => $bookingResponse['PNR'],
                'booking_id' => $bookingResponse['Response']['BookingId'],
                'user_name' => $validatedData['Passengers'][0]['FirstName'] . ' ' . $validatedData['Passengers'][0]['LastName'],
                'username' => $validatedData['Passengers'][0]['Email'],
                'phone_number' => $validatedData['Passengers'][0]['ContactNo'],
                'issued_date' => now()->format('d F Y'),
                
            
                'flight_name' => $bookingResponse['Response']['Segments'][0]['Airline']['AirlineName'], // From Segments -> AirlineName
                'flight_number' => $bookingResponse['Response']['Segments'][0]['Airline']['FlightNumber'], // From Segments -> FlightNumber
                'arrival_to' => $bookingResponse['Response']['Segments'][0]['Destination']['AirportName'], // From Segments -> Destination -> AirportName
                'departure_from' => $bookingResponse['Response']['Segments'][0]['Origin']['AirportName'], // From Segments -> Origin -> AirportName
                
                'total_fare' => $bookingResponse['Response']['Fare']['PublishedFare'], 
                'usd_amount' => $bookingResponse['Response']['Fare']['OfferedFare'], // From Fare -> OfferedFare
                'conversion_rate' => 1, 
                
                'full_route' => $bookingResponse['Response']['Segments'][0]['Origin']['AirportName'] . ' - ' . $bookingResponse['Response']['Segments'][0]['Destination']['AirportName'], // From Segments -> Origin and Destination
                'flight_date' => \Carbon\Carbon::parse($bookingResponse['Response']['Segments'][0]['ArrTime'])->format('d F Y'), // Flight date in the format
            
                'date_of_booking' => now()->toDateString(),
                'passengers' => $validatedData['Passengers'], 
            ];
            
            $ticket['passengers'] = $validatedData['Passengers']; 
            Mail::to($validatedData['email'])->send(new BookingConfirmation($ticket));
           


            
        }
    
        return $response;
    }

    public function bookFlightold(Request $request)
    {
        // CSRF protection is enabled by default in Laravel
        // Rate limiting for booking endpoint
        $key = 'book-flight:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many attempts. Please try again later.',
            ], 429);
        }
        RateLimiter::hit($key, 60);

        // Get token securely
        $token = $this->apiService->getToken();

        // Validate request with stricter rules
        $validatedData = $request->validate([
            'ResultIndex' => 'required|string|max:50|regex:/^[a-zA-Z0-9_-]+$/', // Sanitized
            'Passengers' => 'required|array|min:1|max:9', // Limit number of passengers
            'Passengers.*.Title' => 'required|string|in:Mr,Ms,Mrs',
            'Passengers.*.FirstName' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/', // Sanitized
            'Passengers.*.LastName' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/', // Sanitized
            'Passengers.*.PaxType' => 'required|integer|in:1,2,3', // Adult, Child, Infant
            'Passengers.*.DateOfBirth' => 'required|date|before:today',
            'Passengers.*.Gender' => 'required|integer|in:1,2', // Male, Female
            'Passengers.*.PassportNo' => 'required|string|max:20|regex:/^[a-zA-Z0-9]+$/', // Sanitized
            'Passengers.*.PassportExpiry' => 'required|date|after:today',
            'Passengers.*.AddressLine1' => 'required|string|max:100',
            'Passengers.*.City' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/', // Sanitized
            'Passengers.*.CountryCode' => 'required|string|size:2', // ISO country code
            'Passengers.*.ContactNo' => 'required|string|regex:/^[0-9]{10}$/', // 10-digit phone
            'Passengers.*.Email' => 'required|email|max:255',
            'Passengers.*.IsLeadPax' => 'required|boolean',
            'Passengers.*.Fare' => 'required|array',
            'Passengers.*.Fare.Currency' => 'required|string|in:INR',
            'Passengers.*.Fare.BaseFare' => 'required|numeric|min:0',
            'Passengers.*.Fare.Tax' => 'required|numeric|min:0',
            'Passengers.*.Fare.YQTax' => 'nullable|numeric|min:0',
            'Passengers.*.Fare.AdditionalTxnFeePub' => 'nullable|numeric|min:0',
            'Passengers.*.Fare.AdditionalTxnFeeOfrd' => 'nullable|numeric|min:0',
            'Passengers.*.Fare.OtherCharges' => 'nullable|numeric|min:0',
            'Passengers.*.Fare.Discount' => 'nullable|numeric|min:0',
            'Passengers.*.Fare.PublishedFare' => 'required|numeric|min:0',
            'Passengers.*.Fare.OfferedFare' => 'required|numeric|min:0',
            'Passengers.*.Fare.TdsOnCommission' => 'nullable|numeric|min:0',
            'Passengers.*.Fare.TdsOnPLB' => 'nullable|numeric|min:0',
            'Passengers.*.Fare.TdsOnIncentive' => 'nullable|numeric|min:0',
            'Passengers.*.Fare.ServiceFee' => 'nullable|numeric|min:0',
            'EndUserIp' => 'required|ip',
            'TraceId' => 'required|string|max:50|regex:/^[a-zA-Z0-9_-]+$/', // Sanitized
            'razorpay_payment_id' => 'required|string|max:50|regex:/^[a-zA-Z0-9_-]+$/', // New: Payment ID
            'transaction_id' => 'required|uuid', // New: Transaction ID
        ]);

        try {
            // Verify Razorpay payment
            $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            $payment = $api->payment->fetch($validatedData['razorpay_payment_id']);
            
            if ($payment->status !== 'captured') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not captured.',
                ], 400);
            }

            // Update Razorpay transaction
            $transaction = RazorpayOrder::where('id', $validatedData['transaction_id'])
                ->where('order_id', $payment->order_id)
                ->firstOrFail();
            
            $transaction->update([
                'transaction_id' => $validatedData['razorpay_payment_id'],
                'status' => 'captured',
            ]);

            // Prepare booking payload
            $bookingPayload = [
                'ResultIndex' => $validatedData['ResultIndex'],
                'Passengers' => [],
                'EndUserIp' => $validatedData['EndUserIp'],
                'TokenId' => $token,
                'TraceId' => $validatedData['TraceId'],
            ];

            foreach ($validatedData['Passengers'] as $passenger) {
                $bookingPayload['Passengers'][] = [
                    'Title' => $passenger['Title'],
                    'FirstName' => $passenger['FirstName'],
                    'LastName' => $passenger['LastName'],
                    'PaxType' => $passenger['PaxType'],
                    'DateOfBirth' => $passenger['DateOfBirth'],
                    'Gender' => $passenger['Gender'],
                    'PassportNo' => $passenger['PassportNo'],
                    'PassportExpiry' => $passenger['PassportExpiry'],
                    'AddressLine1' => $passenger['AddressLine1'],
                    'City' => $passenger['City'],
                    'CountryCode' => $passenger['CountryCode'],
                    'ContactNo' => $passenger['ContactNo'],
                    'Email' => $passenger['Email'],
                    'IsLeadPax' => $passenger['IsLeadPax'],
                    'Fare' => [
                        'Currency' => $passenger['Fare']['Currency'],
                        'BaseFare' => $passenger['Fare']['BaseFare'],
                        'Tax' => $passenger['Fare']['Tax'],
                        'YQTax' => $passenger['Fare']['YQTax'] ?? 0,
                        'AdditionalTxnFeePub' => $passenger['Fare']['AdditionalTxnFeePub'] ?? 0,
                        'AdditionalTxnFeeOfrd' => $passenger['Fare']['AdditionalTxnFeeOfrd'] ?? 0,
                        'OtherCharges' => $passenger['Fare']['OtherCharges'] ?? 0,
                        'Discount' => $passenger['Fare']['Discount'] ?? 0,
                        'PublishedFare' => $passenger['Fare']['PublishedFare'],
                        'OfferedFare' => $passenger['Fare']['OfferedFare'],
                        'TdsOnCommission' => $passenger['Fare']['TdsOnCommission'] ?? 0,
                        'TdsOnPLB' => $passenger['Fare']['TdsOnPLB'] ?? 0,
                        'TdsOnIncentive' => $passenger['Fare']['TdsOnIncentive'] ?? 0,
                        'ServiceFee' => $passenger['Fare']['ServiceFee'] ?? 0,
                    ],
                ];
            }

            // Make API request with retry on token expiration
            $response = Http::timeout(100)->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Book', $bookingPayload);

            if ($response->json('Response.Error.ErrorCode') === 6) {
                $token = $this->apiService->authenticate();
                $bookingPayload['TokenId'] = $token;
                $response = Http::timeout(90)->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Book', $bookingPayload);
            }

            if ($response->json('Response.ResponseStatus') === 1) {
                $bookingResponse = $response->json('Response.Response');

                // Store booking details
                FlightBooking::create([
                    'token' => $token,
                    'trace_id' => $validatedData['TraceId'],
                    'user_ip' => $validatedData['EndUserIp'],
                    'pnr' => $bookingResponse['PNR'],
                    'booking_id' => $bookingResponse['BookingId'],
                    'username' => $validatedData['Passengers'][0]['Email'],
                    'user_name' => $validatedData['Passengers'][0]['FirstName'] . ' ' . $validatedData['Passengers'][0]['LastName'],
                    'phone_number' => $validatedData['Passengers'][0]['ContactNo'],
                    'razorpay_transaction_id' => $transaction->id, // Link to Razorpay transaction
                ]);

                // Prepare ticket details
                $ticket = [
                    'pnr' => $bookingResponse['PNR'],
                    'user_name' => $validatedData['Passengers'][0]['FirstName'] . ' ' . $validatedData['Passengers'][0]['LastName'],
                    'username' => $validatedData['Passengers'][0]['Email'],
                    'flight_name' => 'Test Airlines',
                    'flight_number' => 'TA001',
                    'departure_from' => 'Test City',
                    'flight_date' => '2025-04-25 10:00:00',
                    'arrival_to' => 'Test Destination',
                    'total_fare' => $transaction->amount,
                    'booking_id' => $bookingResponse['BookingId'],
                    'phone_number' => $validatedData['Passengers'][0]['ContactNo'],
                    'date_of_booking' => now()->toDateString(),
                ];

                // Send booking confirmation email
                Mail::to($validatedData['Passengers'][0]['Email'])->send(new BookingConfirmation($ticket));

                return response()->json([
                    'status' => 'success',
                    'data' => $bookingResponse,
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Booking failed.',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Flight booking failed: ' . $e->getMessage(), [
                'trace_id' => $validatedData['TraceId'] ?? null,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Booking failed. Please try again.',
            ], 500);
        }
    }

    public function getCalendarFare(Request $request)
    {
        $token = $this->apiService->getToken();
        try {

            $validated = $request->validate([
                'JourneyType' => 'integer',
                'EndUserIp' => 'ip',

                'Segments' => 'required|array',
                'Segments.*.Origin' => 'string|max:3',
                'Segments.*.Destination' => 'required|string|max:3',
                'Segments.*.FlightCabinClass' => 'required|integer',
                'Segments.*.PreferredDepartureTime' => 'required|date',
            ]);


            $apiUrl = "https://tboapi.travelboutiqueonline.com/AirAPI_V10/AirService.svc/GetCalendarFare";


            $response = Http::post($apiUrl, [
                "JourneyType" => $validated['JourneyType'],
                "EndUserIp" => $validated['EndUserIp'],
                "TokenId" => $token,
                "PreferredAirlines" => $request->input('PreferredAirlines', null),
                "Segments" => $validated['Segments'],
                "Sources" => $request->input('Sources', null),
            ]);

            // Check if the response is successful
            if ($response->successful()) {
                return response()->json($response->json());
            }

            // Log error for unsuccessful responses
            Log::error('API returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'Unable to fetch data from external API',
                'details' => $response->json(),
            ], $response->status());
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Catch any other exceptions
            Log::error('An error occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getCancellationCharges(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'BookingId' => 'required|string',
            'RequestType' => 'required|in:1,2',
            'EndUserIp' => 'required|ip',
            'TokenId' => 'required|string',
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $apiUrl = "https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/GetCancellationCharges";

        // Get validated request data
        $requestData = $validator->validated();

        // API request using Laravel HTTP Client
        $response = Http::post($apiUrl, $requestData);

        if ($response->successful()) {
            $data = $response->json();

            return response()->json([
                'status' => $data['Response']['ResponseStatus'] ?? 'N/A',
                'trace_id' => $data['Response']['TraceId'] ?? 'N/A',
                'cancellation_charge' => $data['Response']['CancellationCharge'] ?? 'N/A',
                'refund_amount' => $data['Response']['RefundAmount'] ?? 'N/A',
                'currency' => $data['Response']['Currency'] ?? 'N/A',
                'gst' => $data['Response']['GST'] ?? [],
                'cancel_charge_details' => $data['Response']['CancelChargeDetails'] ?? null
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch data from the API',
            ], 500);
        }
    }


    public function genrateTickBook(Request $request)
    {
        try {
            $token = $this->apiService->getToken();

            // Validate the request data
            $validatedData = $request->validate([
                'ResultIndex' => 'required|string',
                'Passengers' => 'required|array',
                'email' => 'required|email',
                'user_id' => 'required',


                'Passengers.*.Title' => 'required|string',
                'Passengers.*.FirstName' => 'required|string',
                'Passengers.*.LastName' => 'required|string',
                'Passengers.*.PaxType' => 'required|integer',
                'Passengers.*.DateOfBirth' => 'required|date',
                'Passengers.*.Gender' => 'required|integer',
                'Passengers.*.PassportNo' => 'required|string',
                'Passengers.*.PassportExpiry' => 'required|date',
                'Passengers.*.AddressLine1' => 'required|string',
                'Passengers.*.City' => 'required|string',
                'Passengers.*.CountryCode' => 'required|string',
                'Passengers.*.ContactNo' => 'required|string',
                'Passengers.*.Email' => 'required|email',
                'Passengers.*.IsLeadPax' => 'required|boolean',
                'Passengers.*.Fare' => 'required|array',
                'Passengers.*.Fare.Currency' => 'required|string',
                'Passengers.*.Fare.BaseFare' => 'required|numeric',
                'Passengers.*.Fare.Tax' => 'required|numeric',
                'Passengers.*.Fare.YQTax' => 'nullable|numeric',
                'Passengers.*.Fare.AdditionalTxnFeePub' => 'nullable|numeric',
                'Passengers.*.Fare.AdditionalTxnFeeOfrd' => 'nullable|numeric',
                'Passengers.*.Fare.OtherCharges' => 'nullable|numeric',
                'Passengers.*.Fare.Discount' => 'nullable|numeric',
                'Passengers.*.Fare.PublishedFare' => 'required|numeric',
                'Passengers.*.Fare.OfferedFare' => 'required|numeric',
                'Passengers.*.Fare.TdsOnCommission' => 'nullable|numeric',
                'Passengers.*.Fare.TdsOnPLB' => 'nullable|numeric',
                'Passengers.*.Fare.TdsOnIncentive' => 'nullable|numeric',
                'Passengers.*.Fare.ServiceFee' => 'nullable|numeric',
                'EndUserIp' => 'required|string',
                'TraceId' => 'required|string',
            ]);

            // Prepare the booking payload
            $bookingPayload = [
                "ResultIndex" => $validatedData['ResultIndex'],
                "Passengers" => [],
                "EndUserIp" => $validatedData['EndUserIp'],
                "TokenId" => $token,
                "TraceId" => $validatedData['TraceId'],
            ];

            // Loop through passengers
            foreach ($validatedData['Passengers'] as $passenger) {
                $bookingPayload['Passengers'][] = [
                    "Title" => $passenger['Title'],
                    "FirstName" => $passenger['FirstName'],
                    "LastName" => $passenger['LastName'],
                    "PaxType" => $passenger['PaxType'],
                    "DateOfBirth" => $passenger['DateOfBirth'],
                    "Gender" => $passenger['Gender'],
                    "PassportNo" => $passenger['PassportNo'],
                    "PassportExpiry" => $passenger['PassportExpiry'],
                    "AddressLine1" => $passenger['AddressLine1'],
                    "City" => $passenger['City'],
                    "CountryCode" => $passenger['CountryCode'],
                    "ContactNo" => $passenger['ContactNo'],
                    "Email" => $passenger['Email'],
                    "IsLeadPax" => $passenger['IsLeadPax'],
                    "Fare" => [
                        "Currency" => $passenger['Fare']['Currency'],
                        "BaseFare" => $passenger['Fare']['BaseFare'],
                        "Tax" => $passenger['Fare']['Tax'],
                        "YQTax" => $passenger['Fare']['YQTax'] ?? 0,
                        "AdditionalTxnFeePub" => $passenger['Fare']['AdditionalTxnFeePub'] ?? 0.0,
                        "AdditionalTxnFeeOfrd" => $passenger['Fare']['AdditionalTxnFeeOfrd'] ?? 0.0,
                        "OtherCharges" => $passenger['Fare']['OtherCharges'] ?? 0.0,
                        "Discount" => $passenger['Fare']['Discount'] ?? 0.0,
                        "PublishedFare" => $passenger['Fare']['PublishedFare'],
                        "OfferedFare" => $passenger['Fare']['OfferedFare'],
                        "TdsOnCommission" => $passenger['Fare']['TdsOnCommission'] ?? 0,
                        "TdsOnPLB" => $passenger['Fare']['TdsOnPLB'] ?? 0,
                        "TdsOnIncentive" => $passenger['Fare']['TdsOnIncentive'] ?? 0,
                        "ServiceFee" => $passenger['Fare']['ServiceFee'] ?? 0,
                    ],
                ];
            }

            // Make the API request
            $response = Http::timeout(100)->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Ticket', $bookingPayload);

            // Handle API errors
            if ($response->failed()) {
                throw new \Exception('Initial API request failed: ' . $response->body());
            }

            // Handle token expiration
            if ($response->json('Response.Error.ErrorCode') === 6) {
                $token = $this->apiService->authenticate();
                $bookingPayload['TokenId'] = $token;
                $response = Http::timeout(90)->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Ticket', $bookingPayload);

                if ($response->failed()) {
                    throw new \Exception('Retry API request failed after token refresh: ' . $response->body());
                }
            }

            // Check booking status
            if ($response->json('Response.ResponseStatus') !== 1) {
                $errorMessage = $response->json('Response.Error.ErrorMessage') ?? 'Unknown error';

                // Handle duplicate booking error
                if (str_contains($errorMessage, 'Booking is already done for the same criteria')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This booking has already been processed with the same details.',
                        'error' => $errorMessage,
                        'action' => 'Please check your booking history or start a new search.',
                        'pnr' => preg_match('/PNR (\w+)/', $errorMessage, $matches) ? $matches[1] : null, // Extract PNR if present
                    ], 400);
                }

                throw new \Exception('Booking failed: ' . $errorMessage);
            }

            $bookingResponse = $response->json('Response.Response');

            // Store booking details
            Bookflights::create([
                'token' => $token,
                'trace_id' => $validatedData['TraceId'],
                'user_ip' => $validatedData['EndUserIp'],
                'user_id' => $validatedData['user_id'],

                'pnr' => $bookingResponse['PNR'],
                'booking_id' => $bookingResponse['BookingId'],
                'username' => $validatedData['email'],
                'user_name' => $validatedData['Passengers'][0]['FirstName'] . ' ' . $validatedData['Passengers'][0]['LastName'],
                'phone_number' => $validatedData['Passengers'][0]['ContactNo'],
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $response->json(),
                'message' => 'Booking created successfully',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'API request timeout or connection error',
                'error' => $e->getMessage(),
            ], 503);
        } catch (\Exception $e) {
            \Log::error('Booking Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getBookingDetails(Request $request)
    {
        try {
            $token = $this->apiService->getToken();

            // Validate the request data
            $validatedData = $request->validate([
                'EndUserIp' => 'required|string|ip', // Ensures valid IP address
                'TraceId' => 'required|string',
            
                'PNR' => 'required|string',
                'BookingId' => 'required|integer',
            ]);

            // Prepare the payload
            $payload = [
                'EndUserIp' => $validatedData['EndUserIp'],
                'TraceId' => $validatedData['TraceId'],
                'TokenId' => $token, // Use fetched token initially
                'PNR' => $validatedData['PNR'],
                'BookingId' => $validatedData['BookingId'],
            ];

            // Make the API request
            $response = Http::timeout(100)->post(
                'https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/GetBookingDetails',
                $payload
            );

            // Handle API errors
            if ($response->failed()) {
                throw new \Exception('Initial API request failed: ' . $response->body());
            }

            // Handle token expiration (ErrorCode 6)
            if ($response->json('Response.Error.ErrorCode') === 6) {
                $token = $this->apiService->authenticate(); // Refresh token
                $payload['TokenId'] = $token;
                $response = Http::timeout(90)->post(
                    'https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/GetBookingDetails',
                    $payload
                );

                if ($response->failed()) {
                    throw new \Exception('Retry API request failed after token refresh: ' . $response->body());
                }
            }

            // Check response status
            if ($response->json('Response.ResponseStatus') !== 1) {
                $errorMessage = $response->json('Response.Error.ErrorMessage') ?? 'Unknown error';
                throw new \Exception('Failed to fetch booking details: ' . $errorMessage);
            }

            $bookingResponse = $response->json('Response');

            // Optionally store booking details (if you have a model)
            /* Uncomment if you have a BookingDetails model
            BookingDetails::create([
                'token' => $token,
                'trace_id' => $validatedData['TraceId'],
                'user_ip' => $validatedData['EndUserIp'],
                'pnr' => $bookingResponse['FlightItinerary']['PNR'],
                'booking_id' => $bookingResponse['FlightItinerary']['BookingId'],
            ]);
            */

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


    public function generateTicket(Request $request)
    {
        $validatedData = $request->validate([
            'EndUserIp' => 'required|string',
            'TokenId' => 'required|string',
            'TraceId' => 'required|string',
            'PNR' => 'required|string',
            'BookingId' => 'required|integer',
        ]);
    
        $payload = [
            'EndUserIp' => $validatedData['EndUserIp'],
            'TokenId' => $validatedData['TokenId'],
            'TraceId' => trim($validatedData['TraceId']),
            'PNR' => $validatedData['PNR'],
            'BookingId' => $validatedData['BookingId'],
        ];
    
        $response = Http::timeout(90)->post('https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/Ticket', $payload);
    
        return $response->json();
    }

    

    
    public function sendChangeRequest(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'BookingId' => 'required|string',
                'RequestType' => 'required|integer|in:1', // Assuming RequestType is fixed as 1
                'CancellationType' => 'required|integer|in:0', // Assuming CancellationType is fixed as 0
                'Remarks' => 'required|string|max:255',
                'EndUserIp' => 'required|ip',
                'TokenId' => 'required|string',
            ]);

            // Prepare the payload
            $payload = [
                'BookingId' => $validatedData['BookingId'],
                'RequestType' => $validatedData['RequestType'],
                'CancellationType' => $validatedData['CancellationType'],
                'Remarks' => $validatedData['Remarks'],
                'EndUserIp' => $validatedData['EndUserIp'],
                'TokenId' => $validatedData['TokenId'],
            ];

            // Make the API request to TekTravels
            $response = Http::timeout(100)->post(
                'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/SendChangeRequest',
                $payload
            );

            // Check if the request failed
            if ($response->failed()) {
                throw new \Exception('API request failed: ' . $response->body());
            }

            $responseData = $response->json();

            // Check response status
            if ($responseData['Response']['ResponseStatus'] !== 1) {
                throw new \Exception('Failed to process change request: ' . ($responseData['Response']['SupplierErrorMsg'] ?? 'Unknown error'));
            }

            // Return successful response
            return response()->json([
                'status' => 'success',
                'data' => $responseData['Response'],
                'message' => 'Change request processed successfully',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'API request timeout or connection error',
                'error' => $e->getMessage(),
            ], 503);
        } catch (\Exception $e) {
            Log::error('SendChangeRequest Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the change request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }






    public function searchreturnflight(Request $request)
    {

        $token = $this->apiService->getToken();

        $validatedData = $request->validate([
            "EndUserIp" => 'required',
            'AdultCount' => 'required|integer',
            'Origin' => 'required|string',
            'Destination' => 'required|string',
            'FlightCabinClass' => 'required|integer',
            'PreferredDepartureTime' => 'required',
            'PreferredDepartureTime2' => 'required',
            'ChildCount' => 'nullable|integer',
            'InfantCount' => 'nullable|integer',
            'DirectFlight' => 'nullable|boolean',
            'OneStopFlight' => 'nullable|boolean',
            'JourneyType' => 'required|integer',
            'PreferredAirlines' => 'nullable|string',

        ]);

        // Prepare the search payload with the validated data and token
        $searchPayload = [
            "EndUserIp" => $validatedData['EndUserIp'],
            "TokenId" => $token,
            "AdultCount" => $validatedData['AdultCount'],
            "ChildCount" => $validatedData['ChildCount'],
            "InfantCount" => $validatedData['InfantCount'],
            "DirectFlight" => $validatedData['DirectFlight'],
            "OneStopFlight" => $validatedData['OneStopFlight'],
            "JourneyType" => $validatedData['JourneyType'],
            "PreferredAirlines" => $validatedData['PreferredAirlines'],
            "Segments" => [
                [
                    "Origin" => $validatedData['Origin'],
                    "Destination" => $validatedData['Destination'],
                    "FlightCabinClass" => $validatedData['FlightCabinClass'],
                    "PreferredDepartureTime" => $validatedData['PreferredDepartureTime'],
                    "PreferredArrivalTime" => $validatedData['PreferredDepartureTime']                 // "PreferredDepartureTime" =>$validatedData['PreferredDepartureTime'],
                    // "PreferredArrivalTime" =>$validatedData['PreferredDepartureTime']
                ],
                [
                    "Origin" => $validatedData['Destination'],
                    "Destination" => $validatedData['Origin'],
                    "FlightCabinClass" => $validatedData['FlightCabinClass'],
                    "PreferredDepartureTime" => $validatedData['PreferredDepartureTime2'],
                    "PreferredArrivalTime" => $validatedData['PreferredDepartureTime2']

                ]
            ],
            "Sources" => null
        ];


        $response = Http::timeout(100)->withHeaders([])->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Search', $searchPayload);



        if ($response->json('Response.Error.ErrorCode') === 6) {

            $token = $this->apiService->authenticate();


            $response = Http::timeout(90)->withHeaders([])->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Search', $searchPayload);
        }

        //  Return the search response
        return $response->json();
    }






    public function advance_search(Request $request)
    {

        $token = $this->apiService->getToken();

        $validatedData = $request->validate([
            "EndUserIp" => 'required',
            'AdultCount' => 'required|integer',
            'ChildCount' => 'nullable|integer',
            'InfantCount' => 'nullable|integer',
            "TraceId" => "required",
            "ResultIndex" => "required",
            "Source" => "required",
            "IsLCC" => "required",
            "IsRefundable" => "required",
            "AirlineRemark" => "nullable",
            "TripIndicator" => "required",
            "SegmentIndicator" => "required",
            "AirlineCode" => "required",
            "AirlineName" => "required",
            "FlightNumber" => "required",
            "FareClass" => "required",
            "OperatingCarrier" => "nullable"
        ]);

        // Prepare the search payload with the validated data and token
        $searchPayload = [
            'AdultCount' =>  $validatedData['AdultCount'],
            'ChildCount' =>  $validatedData['ChildCount'],
            'InfantCount' =>  $validatedData['InfantCount'],
            'EndUserIp' =>  $validatedData['EndUserIp'],
            'TokenId' =>  $token,
            'TraceId' =>  $validatedData['TraceId'],
            'AirSearchResult' => [
                [
                    'ResultIndex' =>  $validatedData['ResultIndex'],
                    'Source' =>  $validatedData['Source'],
                    'IsLCC' =>  $validatedData['IsLCC'],
                    'IsRefundable' => $validatedData['IsRefundable'],
                    'AirlineRemark' =>  $validatedData['AirlineRemark'],
                    'Segments' => [
                        [
                            [
                                'TripIndicator' => $validatedData['TripIndicator'],
                                'SegmentIndicator' => $validatedData['SegmentIndicator'],
                                'Airline' => [
                                    'AirlineCode' => $validatedData['AirlineCode'],
                                    'AirlineName' => $validatedData['AirlineName'],
                                    'FlightNumber' => $validatedData['FlightNumber'],
                                    'FareClass' => $validatedData['FareClass'],
                                    'OperatingCarrier' => $validatedData['OperatingCarrier'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];


        $response = Http::timeout(100)->withHeaders([])->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/PriceRBD', $searchPayload);



        if ($response->json('Response.Error.ErrorCode') === 6) {

            $token = $this->apiService->authenticate();


            $response = Http::timeout(90)->withHeaders([])->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/PriceRBD', $searchPayload);
        }

        //  Return the search response
        return $response->json();
    }


   public function fareRules(Request  $request)
    {
        $token = $this->apiService->getToken();

        $validatedData = $request->validate([
            "EndUserIp" => "required",
            "TraceId" => "required|string",
            "ResultIndex" => "required|string"

        ]);
        $validatedData["TokenId"] = $token;

        $response = Http::timeout(100)->withHeaders([])->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/FareRule', $validatedData);
        if ($response->json('Response.Error.ErrorCode') === 6) {

            $token = $this->apiService->authenticate();


            $response = Http::timeout(100)->withHeaders([])->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/FareRule', $validatedData);
        }
        return $response;
    }







    function ssrrequest(Request $request)
    {
        $token = $this->apiService->getToken();


        $validatedData = $request->validate([
            "EndUserIp" => 'required',
            "TraceId" => "required",
            "ResultIndex" => "required",
        ]);

        $searchpayload = [
            "EndUserIp" => $validatedData["EndUserIp"],
            "TokenId" => $token,
            "TraceId" => $validatedData["TraceId"],
            "ResultIndex" => $validatedData["ResultIndex"]
        ];


        $response = Http::timeout(100)->withHeaders([])->post('https://tboapi.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/SSR', $searchpayload);
        if ($response->json('Response.Error.ErrorCode') === 6) {

            $token = $this->apiService->authenticate();


            $response = Http::timeout(100)->withHeaders([])->post('https://tboapi.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/SSR', $searchpayload);
        }
        return $response;
    }



     function farequate(Request  $request)
    {
        $token = $this->apiService->getToken();

        $validatedData = $request->validate([
            "EndUserIp" => "required",
            "TraceId" => "required|string",
            "ResultIndex" => "required|string"

        ]);
        $validatedData["TokenId"] = $token;

        $response = Http::timeout(100)->withHeaders([])->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/FareQuote', $validatedData);
        if ($response->json('Response.Error.ErrorCode') === 6) {

            $token = $this->apiService->authenticate();


            $response = Http::timeout(100)->withHeaders([])->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/FareQuote', $validatedData);
        }
        return $response;
    }
}


