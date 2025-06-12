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

use App\Mail\BookingConfirmationMail;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
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
    try {
        $token = $this->apiService->getToken();

        // Log raw request data for debugging
        Log::info('Raw Request Data', ['data' => $request->all()]);

        // Validate the request data, including Fare and root-level email
        $validatedData = $request->validate([
            'ResultIndex' => 'required|string',
            'Passengers' => 'required|array',
            'email' => 'required|email', // Added validation for root-level email
            'Passengers.*.Title' => 'required|string',
            'Passengers.*.FirstName' => 'required|string',
            'Passengers.*.LastName' => 'required|string',
            'Passengers.*.PaxType' => 'required|integer',
            'Passengers.*.DateOfBirth' => 'required|date',
            'Passengers.*.Gender' => 'required|integer',
            'Passengers.*.PassportNo' => 'nullable|string',
            'Passengers.*.PassportExpiry' => 'nullable|date',
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

        // Log validated data for debugging
        Log::info('Validated Data', ['data' => $validatedData]);

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

        // Handle API errors
        if ($response->failed()) {
            throw new \Exception('Initial API request failed: ' . $response->body());
        }

        // Handle token expiration
        if ($response->json('Response.Error.ErrorCode') === 6) {
            $token = $this->apiService->authenticate();
            $bookingPayload['TokenId'] = $token;
            $response = Http::timeout(90)->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Book', $bookingPayload);

            if ($response->failed()) {
                throw new \Exception('Retry API request failed after token refresh: ' . $response->body());
            }
        }

        // Check if the booking was successful
        if ($response->json('Response.ResponseStatus') !== 1) {
            $errorMessage = $response->json('Response.Error.ErrorMessage') ?? 'Unknown error';
            return response()->json([
                'status' => 'error',
                'message' => 'Booking failed',
                'error' => $errorMessage,
            ], 400);
        }

        $bookingResponse = $response->json('Response.Response');

        Log::info('Booking Response', ['data' => $bookingResponse]);

        // Store booking details
        Bookflights::create([
            'token' => $token,
            'trace_id' => $validatedData['TraceId'],
            'user_ip' => $validatedData['EndUserIp'],
            'pnr' => $bookingResponse['PNR'] ?? null,
            'booking_id' => $bookingResponse['BookingId'] ?? null,
            'username' => $validatedData['email'], // Use validated root-level email
            'user_name' => $validatedData['Passengers'][0]['FirstName'] . ' ' . $validatedData['Passengers'][0]['LastName'],
            'phone_number' => $validatedData['Passengers'][0]['ContactNo'],
        ]);

        // Prepare invoice data
        $invoiceData = [
            'InvoiceNo' => $bookingResponse['FlightItinerary']['InvoiceNo'] ?? 'N/A',
            'InvoiceAmount' => $bookingResponse['FlightItinerary']['InvoiceAmount'] ?? 0,
            'InvoiceCreatedOn' => $bookingResponse['FlightItinerary']['InvoiceCreatedOn'] ?? 'N/A',
            'Currency' => $bookingResponse['FlightItinerary']['Fare']['Currency'] ?? 'INR',
            'BaseFare' => $bookingResponse['FlightItinerary']['Fare']['BaseFare'] ?? 0,
            'Tax' => $bookingResponse['FlightItinerary']['Fare']['Tax'] ?? 0,
            'OtherCharges' => $bookingResponse['FlightItinerary']['Fare']['OtherCharges'] ?? 0,
        ];

        $passengerData = $bookingResponse['FlightItinerary']['Passenger'] ?? [];

        // Prepare ticket data (fix response paths)
        $ticket = [
            'pnr' => $bookingResponse['PNR'] ?? 'N/A',
            'booking_id' => $bookingResponse['BookingId'] ?? 'N/A',
            'user_name' => $validatedData['Passengers'][0]['FirstName'] . ' ' . $validatedData['Passengers'][0]['LastName'],
            'username' => $validatedData['Passengers'][0]['Email'], // Use passenger Email
            'phone_number' => $validatedData['Passengers'][0]['ContactNo'],
            'issued_date' => now()->format('d F Y'),
            'flight_name' => $bookingResponse['FlightItinerary']['Segments'][0]['Airline']['AirlineName'] ?? 'N/A', // Fixed path
            'flight_number' => $bookingResponse['FlightItinerary']['Segments'][0]['Airline']['FlightNumber'] ?? 'N/A', // Fixed path
            'arrival_to' => $bookingResponse['FlightItinerary']['Segments'][0]['Destination']['AirportName'] ?? 'N/A', // Fixed path
            'departure_from' => $bookingResponse['FlightItinerary']['Segments'][0]['Origin']['AirportName'] ?? 'N/A', // Fixed path
            'total_fare' => $bookingResponse['FlightItinerary']['Fare']['PublishedFare'] ?? 0, // Fixed path
            'usd_amount' => $bookingResponse['FlightItinerary']['Fare']['OfferedFare'] ?? 0, // Fixed path
            'conversion_rate' => 1,
            'full_route' => ($bookingResponse['FlightItinerary']['Segments'][0]['Origin']['AirportName'] ?? 'N/A') . ' - ' . ($bookingResponse['FlightItinerary']['Segments'][0]['Destination']['AirportName'] ?? 'N/A'), // Fixed path
            'flight_date' => isset($bookingResponse['FlightItinerary']['Segments'][0]['ArrTime'])
                ? \Carbon\Carbon::parse($bookingResponse['FlightItinerary']['Segments'][0]['ArrTime'])->format('d F Y')
                : 'N/A', // Fixed path
            'date_of_booking' => now()->toDateString(),
            'passengers' => $validatedData['Passengers'],
        ];

        // Prepare booking data for email
        $bookingData = [
            'PNR' => $bookingResponse['PNR'] ?? 'N/A',
            'BookingId' => $bookingResponse['BookingId'] ?? 'N/A',
            'Origin' => $bookingResponse['FlightItinerary']['Segments'][0]['Origin']['CityName'] ?? 'N/A',
            'Destination' => $bookingResponse['FlightItinerary']['Segments'][0]['Destination']['CityName'] ?? 'N/A',
            'AirlineCode' => $bookingResponse['FlightItinerary']['AirlineCode'] ?? 'N/A',
            'AirlineName' => $bookingResponse['FlightItinerary']['Segments'][0]['Airline']['AirlineName'] ?? 'N/A',
            'FlightNumber' => $bookingResponse['FlightItinerary']['Segments'][0]['Airline']['FlightNumber'] ?? 'N/A',
            'DepTime' => $bookingResponse['FlightItinerary']['Segments'][0]['Origin']['DepTime'] ?? 'N/A',
            'ArrTime' => $bookingResponse['FlightItinerary']['Segments'][0]['Destination']['ArrTime'] ?? 'N/A',
            'Segments' => $bookingResponse['FlightItinerary']['Segments'] ?? [],
        ];

        // Send email
        Mail::to($validatedData['email'])->send(new BookingConfirmationMail($bookingData, $passengerData, $invoiceData));

        return response()->json([
            'status' => 'success',
            'data' => $bookingResponse,
            'message' => 'Booking created successfully and confirmation email sent',
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'error' => $e->errors(),
        ], 422);
    } catch (\Illuminate\Http\Client\RequestException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'API request timeout or connection error',
            'error' => $e->getMessage(),
        ], 503);
    } catch (\Exception $e) {
        Log::error('Booking Error: ' . $e->getMessage(), [
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
           'Passengers.*.PassportNo' => 'nullable|string', 
            'Passengers.*.PassportExpiry' => 'nullable|date',
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
            $response = Http::timeout(100)->post('https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/Book', $bookingPayload);

            if ($response->json('Response.Error.ErrorCode') === 6) {
                $token = $this->apiService->authenticate();
                $bookingPayload['TokenId'] = $token;
                $response = Http::timeout(90)->post('https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/Book', $bookingPayload);
            }

            if ($response->json('Response.ResponseStatus') === 1) {
                $bookingResponse = $response->json('Response.Response');

                // Store booking details
                Bookflights::create([
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
                Mail::to($validatedData['Email'])->send(new BookingConfirmation($ticket));

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


            $apiUrl = "https://tboapi.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/GetCalendarFare";


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
    try {
        // Fetch token
        $token = $this->apiService->getToken();

        // Validation rules
        $validator = Validator::make($request->all(), [
            'BookingId' => 'required|string',
            'RequestType' => 'required|in:1,2',
            'EndUserIp' => 'required|ip',
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $apiUrl = "http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/GetCancellationCharges";

        // Get validated request data
        $requestData = array_merge($validator->validated(), ['TokenId' => $token]);

        // API request using Laravel HTTP Client
        $response = Http::timeout(90)->post($apiUrl, $requestData);

        if ($response->successful()) {
            $data = $response->json();

            // Check API response status
            if ($data['Response']['ResponseStatus'] !== 1) {
                return response()->json([
                    'status' => false,
                    'message' => $data['Response']['Error']['ErrorMessage'] ?? 'Failed to fetch cancellation charges',
                ], 400);
            }

            // Prepare response data
            $cancellationData = [
                'status' => $data['Response']['ResponseStatus'] ?? 'N/A',
                'trace_id' => $data['Response']['TraceId'] ?? 'N/A',
                'cancellation_charge' => $data['Response']['CancellationCharge'] ?? 0,
                'refund_amount' => $data['Response']['RefundAmount'] ?? 0,
                'currency' => $data['Response']['Currency'] ?? 'INR',
                'gst' => $data['Response']['GST'] ?? [],
                'cancel_charge_details' => $data['Response']['CancelChargeDetails'] ?? null,
            ];

            // Update Bookflights with cancellation details
            Bookflights::where('booking_id', $requestData['BookingId'])->update([
                'cancellation_charge' => $cancellationData['cancellation_charge'],
                'refund_amount' => $cancellationData['refund_amount'],
                'currency' => $cancellationData['currency'],
    
            ]);

            return response()->json($cancellationData);
        } else {
            // Handle token expiration
            if ($response->json('Response.Error.ErrorCode') === 6) {
                $token = $this->apiService->authenticate();
                $requestData['TokenId'] = $token;
                $response = Http::timeout(90)->post($apiUrl, $requestData);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['Response']['ResponseStatus'] !== 1) {
                        return response()->json([
                            'status' => false,
                            'message' => $data['Response']['Error']['ErrorMessage'] ?? 'Failed to fetch cancellation charges after token refresh',
                        ], 400);
                    }

                    $cancellationData = [
                        'status' => $data['Response']['ResponseStatus'] ?? 'N/A',
                        'trace_id' => $data['Response']['TraceId'] ?? 'N/A',
                        'cancellation_charge' => $data['Response']['CancellationCharge'] ?? 0,
                        'refund_amount' => $data['Response']['RefundAmount'] ?? 0,
                        'currency' => $data['Response']['Currency'] ?? 'INR',
                        'gst' => $data['Response']['GST'] ?? [],
                        'cancel_charge_details' => $data['Response']['CancelChargeDetails'] ?? null,
                    ];

                    // Update Bookflights with cancellation details
                    Bookflights::where('booking_id', $requestData['BookingId'])->update([
                        'cancellation_charge' => $cancellationData['cancellation_charge'],
                        'refund_amount' => $cancellationData['refund_amount'],
                        'currency' => $cancellationData['currency'],
                        'cancellation_trace_id' => $cancellationData['trace_id'],
                    ]);

                    return response()->json($cancellationData);
                }
            }

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch data from the API: ' . $response->body(),
            ], 500);
        }
    } catch (\Exception $e) {
        Log::error('Cancellation Charges Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching cancellation charges',
            'error' => $e->getMessage(),
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
                'Passengers.*.PassportNo' => 'nullable|string',
                'Passengers.*.PassportExpiry' => 'nullable|date',
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
            Log::info('Booking Response', $bookingPayload);
            
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
                        'pnr' => preg_match('/PNR (\w+)/', $errorMessage, $matches) ? $matches[1] : null,
                    ], 400);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking failed',
                    'error' => $errorMessage,
                ], 400);
            }

            $bookingResponse = $response->json('Response.Response');

            Log::info('Booking Response', ['data' => $bookingResponse]);

            // Store booking details
            Bookflights::create([
                'token' => $token,
                'trace_id' => $validatedData['TraceId'],
                'user_ip' => $validatedData['EndUserIp'],
                'user_id' => $validatedData['user_id'],
                'user_number' => $validatedData['Passengers'][0]['ContactNo'],
                'user_name' => $validatedData['email'],
                'pnr' => $bookingResponse['PNR'] ?? null,
                'booking_id' => $bookingResponse['BookingId'] ?? null,
                'flight_name' => $bookingResponse['FlightItinerary']['Segments'][0]['Airline']['AirlineName'] ?? null,
                'departure_from' => $bookingResponse['FlightItinerary']['Segments'][0]['Origin']['CityName'] ?? null,
                'arrival_to' => $bookingResponse['FlightItinerary']['Segments'][0]['Destination']['CityName'] ?? null,
                'flight_date' => isset($bookingResponse['FlightItinerary']['Segments'][0]['Origin']['DepTime'])
                    ? \Carbon\Carbon::parse($bookingResponse['FlightItinerary']['Segments'][0]['Origin']['DepTime'])->toDateString()
                    : null,
                'date_of_booking' => now(),
                'phone_number' => $validatedData['Passengers'][0]['ContactNo'],
                'airline_code' => $bookingResponse['FlightItinerary']['AirlineCode'] ?? null,
                'flight_number' => $bookingResponse['FlightItinerary']['Segments'][0]['Airline']['FlightNumber'] ?? null,
                'departure_time' => $bookingResponse['FlightItinerary']['Segments'][0]['Origin']['DepTime'] ?? null,
                'arrival_time' => $bookingResponse['FlightItinerary']['Segments'][0]['Destination']['ArrTime'] ?? null,
                'duration' => $bookingResponse['FlightItinerary']['Segments'][0]['Duration'] ?? null,
                'commission_earned' => $bookingResponse['FlightItinerary']['Fare']['CommissionEarned'] ?? 0.0,
                'segments' => $bookingResponse['FlightItinerary']['Segments'] ?? [], // Store all segments as JSON
            ]);

            $bookingData = [
                'PNR' => $bookingResponse['PNR'] ?? 'N/A',
                'BookingId' => $bookingResponse['BookingId'] ?? 'N/A',
                'Origin' => $bookingResponse['FlightItinerary']['Segments'][0]['Origin']['CityName'] ?? 'N/A',
                'Destination' => $bookingResponse['FlightItinerary']['Segments'][0]['Destination']['CityName'] ?? 'N/A',
                'AirlineCode' => $bookingResponse['FlightItinerary']['AirlineCode'] ?? 'N/A',
                'AirlineName' => $bookingResponse['FlightItinerary']['Segments'][0]['Airline']['AirlineName'] ?? 'N/A',
                'FlightNumber' => $bookingResponse['FlightItinerary']['Segments'][0]['Airline']['FlightNumber'] ?? 'N/A',
                'DepTime' => $bookingResponse['FlightItinerary']['Segments'][0]['Origin']['DepTime'] ?? 'N/A',
                'ArrTime' => $bookingResponse['FlightItinerary']['Segments'][0]['Destination']['ArrTime'] ?? 'N/A',
                'Segments' => $bookingResponse['FlightItinerary']['Segments'] ?? [],
            ];

            $passengerData = $bookingResponse['FlightItinerary']['Passenger'] ?? [];

            $invoiceData = [
                'InvoiceNo' => $bookingResponse['FlightItinerary']['InvoiceNo'] ?? 'N/A',
                'InvoiceAmount' => $bookingResponse['FlightItinerary']['InvoiceAmount'] ?? 0,
                'InvoiceCreatedOn' => $bookingResponse['FlightItinerary']['InvoiceCreatedOn'] ?? 'N/A',
                'Currency' => $bookingResponse['FlightItinerary']['Fare']['Currency'] ?? 'INR',
                'BaseFare' => $bookingResponse['FlightItinerary']['Fare']['BaseFare'] ?? 0,
                'Tax' => $bookingResponse['FlightItinerary']['Fare']['Tax'] ?? 0,
                'OtherCharges' => $bookingResponse['FlightItinerary']['Fare']['OtherCharges'] ?? 0,
            ];

            // Send email
            Mail::to($validatedData['email'])->send(new BookingConfirmationMail($bookingData, $passengerData, $invoiceData));

            return response()->json([
                'status' => 'success',
                'data' => $bookingResponse,
                'message' => 'Booking created successfully and confirmation email sent',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'API request timeout or connection error',
                'error' => $e->getMessage(),
            ], 503);
        } catch (\Exception $e) {
            Log::error('Booking Error: ' . $e->getMessage(), [
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

            // Define validation rules
            $rules = [
                'EndUserIp' => 'string|ip',
                'TraceId' => 'nullable|string',
                'PNR' => '|string',
                'BookingId' => 'nullable|integer|min:1',
                'FirstName' => 'nullable|string',
                'LastName' => 'nullable|string',
            ];

            // Custom validation to ensure at least one of BookingId, PNR, or TraceId is provided
            $validator = Validator::make($request->all(), $rules, [], [
                'EndUserIp' => 'End User IP',
                'TraceId' => 'Trace ID',
                'PNR' => 'PNR',
                'BookingId' => 'Booking ID',
                'FirstName' => 'First Name',
                'LastName' => 'Last Name',
            ]);

            // Add custom validation for required fields based on request cases
            $validator->after(function ($validator) use ($request) {
                $data = $request->all();
                
                // Check if at least one of BookingId, PNR, or TraceId is provided
                if (empty($data['BookingId']) && empty($data['PNR']) && empty($data['TraceId'])) {
                    $validator->errors()->add('BookingId', 'At least one of BookingId, PNR, or TraceId is required.');
                }

                // For cases involving PNR with FirstName and/or LastName (cases 3, 4, 5)
                if (!empty($data['PNR']) && (isset($data['FirstName']) || isset($data['LastName']))) {
                    if (empty($data['FirstName']) || empty($data['LastName'])) {
                        $validator->errors()->add('FirstName', 'Both FirstName and LastName are required when PNR is provided with either.');
                        $validator->errors()->add('LastName', 'Both FirstName and LastName are required when PNR is provided with either.');
                    }
                }
            });

            // Return validation errors if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Prepare the payload based on provided inputs
            $validatedData = $validator->validated();
            $payload = [
                'EndUserIp' => $validatedData['EndUserIp'],
                'TokenId' => $token,
            ];

            // Add fields to payload based on request case
            if (!empty($validatedData['TraceId'])) {
                $payload['TraceId'] = $validatedData['TraceId'];
            }
            if (!empty($validatedData['PNR'])) {
                $payload['PNR'] = $validatedData['PNR'];
            }
            if (!empty($validatedData['BookingId'])) {
                $payload['BookingId'] = $validatedData['BookingId'];
            }
            if (!empty($validatedData['FirstName'])) {
                $payload['FirstName'] = $validatedData['FirstName'];
            }
            if (!empty($validatedData['LastName'])) {
                $payload['LastName'] = $validatedData['LastName'];
            }

            // Make the API request
            $response = Http::timeout(100)->post(
                'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/GetBookingDetails',
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
                    'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/GetBookingDetails',
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
    public function sendBookingConfirmationEmail(Request $request)
{
    try {
        // Validate the request data
        $validatedData = $request->validate([
            'email' => 'required|email',
            'bookingData' => 'required|array',
            'bookingData.PNR' => 'nullable|string',
            'bookingData.BookingId' => 'nullable|string',
            'bookingData.Origin' => 'nullable|string',
            'bookingData.Destination' => 'nullable|string',
            'bookingData.AirlineCode' => 'nullable|string',
            'bookingData.AirlineName' => 'nullable|string',
            'bookingData.FlightNumber' => 'nullable|string',
            'bookingData.DepTime' => 'nullable|string',
            'bookingData.ArrTime' => 'nullable|string',
            'bookingData.Segments' => 'nullable|array',
            'passengerData' => 'nullable|array',
            'invoiceData' => 'required|array',
            'invoiceData.InvoiceNo' => 'nullable|string',
            'invoiceData.InvoiceAmount' => 'nullable|numeric',
            'invoiceData.InvoiceCreatedOn' => 'nullable|string',
            'invoiceData.Currency' => 'nullable|string',
            'invoiceData.BaseFare' => 'nullable|numeric',
            'invoiceData.Tax' => 'nullable|numeric',
            'invoiceData.OtherCharges' => 'nullable|numeric',
            'invoiceData.CommissionEarned' => 'nullable|numeric',
        ]);

        // Send email
        Mail::to($validatedData['email'])->send(new BookingConfirmationMail(
            $validatedData['bookingData'],
            $validatedData['passengerData'],
            $validatedData['invoiceData']
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Confirmation email sent successfully',
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Email Sending Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send confirmation email',
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
    
        $response = Http::timeout(90)->post('http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/Ticket', $payload);
    
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
                'https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/SendChangeRequest',
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


        $response = Http::timeout(100)->withHeaders([])->post('https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/Search', $searchPayload);



        if ($response->json('Response.Error.ErrorCode') === 6) {

            $token = $this->apiService->authenticate();


            $response = Http::timeout(90)->withHeaders([])->post('https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/Search', $searchPayload);
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


        $response = Http::timeout(100)->withHeaders([])->post('https://booking.travelboutiqueonline.com/AirAPI_V10/AirService.svc/rest/PriceRBD', $searchPayload);



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


   public function cancelTicket(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'BookingId' => 'required|string',
                'PNR' => 'required|string|alpha_num',
                'EndUserIp' => 'required|ip',
                'Remarks' => 'required|string|max:255',
            ]);

            // Get token from ApiService
            $token = $this->apiService->getToken();
            if (!$token) {
                throw new \Exception('Failed to retrieve valid token from ApiService.');
            }

            // Prepare the payload for the cancellation request
            $payload = [
                'BookingId' => $validatedData['BookingId'],
                'RequestType' => 1, // Cancellation request
                'CancellationType' => 0, // Full cancellation
                'Remarks' => $validatedData['Remarks'],
                'EndUserIp' => $validatedData['EndUserIp'],
                'TokenId' => $token,
            ];

            // Log the request payload
            Log::debug('CancelTicket Request Payload', [
                'payload' => $payload,
                'request' => $request->all(),
            ]);

            // Make the API request to TekTravels for cancellation
            $response = Http::timeout(100)->post(
                'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/GetBookingDetails',
                $payload
            );

            // Log the raw response
            Log::debug('CancelTicket Raw Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Check if the request failed
            if ($response->failed()) {
                throw new \Exception('API request failed with status ' . $response->status() . ': ' . $response->body());
            }

            $responseData = $response->json();

            // Log the parsed JSON response
            Log::debug('CancelTicket Parsed Response', [
                'response' => $responseData,
            ]);

            // Handle token expiration
            if (isset($responseData['Response']['Error']['ErrorCode']) && $responseData['Response']['Error']['ErrorCode'] === 6) {
                Log::info('Token expired, refreshing token.');
                $token = $this->apiService->authenticate();
                if (!$token) {
                    throw new \Exception('Failed to refresh token from ApiService.');
                }
                $payload['TokenId'] = $token;

                // Retry the request
                $response = Http::timeout(90)->post(
                    'http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/GetBookingDetails',
                    $payload
                );

                // Log the retry response
                Log::debug('CancelTicket Retry Raw Response', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if ($response->failed()) {
                    throw new \Exception('Retry API request failed with status ' . $response->status() . ': ' . $response->body());
                }
                $responseData = $response->json();

                Log::debug('CancelTicket Retry Parsed Response', [
                    'response' => $responseData,
                ]);
            }

            // Check response status and handle errors
            if (!isset($responseData['Response']['ResponseStatus']) || $responseData['Response']['ResponseStatus'] !== 1) {
                $errorMessage = $responseData['Response']['SupplierErrorMsg'] ??
                                $responseData['Response']['Error']['ErrorMessage'] ??
                                json_encode($responseData['Response']['Error'] ?? 'Unknown error');
                throw new \Exception('Failed to process cancellation: ' . $errorMessage);
            }

            // Update the Bookflights table to reflect cancellation
            $booking = Bookflights::where('booking_id', $validatedData['BookingId'])
                ->where('pnr', $validatedData['PNR'])
                ->first();

            if (!$booking) {
                throw new \Exception('Booking not found in database.');
            }

            DB::beginTransaction();
            try {
                $booking->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_remarks' => $validatedData['Remarks'],
                ]);

                // Initiate refund process (placeholder - implement your refund logic here)
                $refundAmount = $this->calculateRefundAmount($responseData['Response']['FlightItinerary']);
                $this->processRefund($booking, $refundAmount);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw new \Exception('Failed to update booking or process refund: ' . $e->getMessage());
            }

            // Log the successful cancellation
            Log::info('Ticket cancellation successful', [
                'BookingId' => $validatedData['BookingId'],
                'PNR' => $validatedData['PNR'],
                'response' => $responseData,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $responseData['Response'],
                'message' => 'Cancellation request processed successfully. Refund initiated.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('CancelTicket Validation Error', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('CancelTicket API Request Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'API request timeout or connection error',
                'error' => $e->getMessage(),
            ], 503);
        } catch (\Exception $e) {
            Log::error('CancelTicket General Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the cancellation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function calculateRefundAmount($flightItinerary)
    {
        // Extract fare details
        $publishedFare = $flightItinerary['Fare']['PublishedFare'] ?? 0;
        $taxes = $flightItinerary['Fare']['Tax'] ?? 0;
        $cancellationCharges = 0; // Placeholder: Fetch actual cancellation charges from API if available

        // As per fare rules, only statutory taxes are refundable for Saver fare
        return $taxes; // Adjust based on actual cancellation charges if provided
    }

    private function processRefund($booking, $refundAmount)
    {
        // Placeholder for refund logic
        // Implement your payment gateway integration here (e.g., Razorpay, Stripe)
        // Example: Initiate refund via payment gateway API
        Log::info('Refund initiated', [
            'booking_id' => $booking->booking_id,
            'pnr' => $booking->pnr,
            'refund_amount' => $refundAmount,
        ]);

        // Update booking with refund details
        $booking->update([
            'refund_amount' => $refundAmount,
            'refund_status' => 'initiated',
            'refund_initiated_at' => now(),
        ]);
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


