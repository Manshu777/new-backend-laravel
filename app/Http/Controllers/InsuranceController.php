<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Mail\InsuranceBookingConfirmation;

use App\Models\BookInsurace;

/**
 * Controller for handling insurance-related API requests.
 */
class InsuranceController extends Controller
{
    protected $apiService;

    /**
     * InsuranceController constructor.
     *
     * @param ApiService $apiService
     */
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Search for insurance plans based on provided criteria.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
   /**
 * Search for insurance plans based on provided criteria.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */ 

    /**
 * Search for insurance plans based on provided criteria.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */

        public function searchInsurance(Request $request)
{
    try {
        // Validate request parameters
        $validated = $request->validate([
            'EndUserIp'       => 'required|ip',
            'PlanCategory'    => 'required|integer|in:1,2,3,4,5,6',
            'PlanCoverage'    => 'required|integer|in:1,2,3,4,5,6,7,8',
            'PlanType'        => 'required|integer|in:1,2',
            'TravelStartDate' => 'required',
            'TravelEndDate'   => 'required',
            'NoOfPax'         => 'required|integer|min:1',
            'PaxAge'          => 'required|array|min:1',
            'PaxAge.*'        => 'required|integer|min:1|max:100', 
        ]);

        // Get API Token
        $token = $this->apiService->getToken();
        $validated["TokenId"] = $token;

        // Define API endpoint
        $apiUrl = "https://api.travelboutiqueonline.com/InsuranceAPI_V1/InsuranceService.svc/rest/Search";

        // Send request to TekTravels API
        $response = Http::post($apiUrl, $validated);

        // Check if API response is valid
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'API request failed. Please try again later.',
                'error'   => $response->body(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'data'    => $response->json(),
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Illuminate\Http\Client\RequestException $e) {
        // Handle HTTP client errors
        return response()->json([
            'success' => false,
            'message' => 'Failed to communicate with the insurance API',
            'error'   => $e->getMessage(),
        ], 500);

    } catch (\Exception $e) {
        // Catch all other errors
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


    /**
     * Book an insurance policy.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */



    public function bookInsurance(Request $request)
{
    try {
        // Get authentication token
        $token = $this->apiService->getToken();
        if (!$token) {
            Log::error('Failed to retrieve authentication token for booking', [
                'timestamp' => now()->toDateTimeString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve authentication token',
            ], 500);
        }

        // Validate request with stricter rules
        $validatedData = $request->validate([
            'TraceId' => 'required|string',
            'ResultIndex' => 'required|integer',
            'Passenger' => 'required|array|min:1',
            'Passenger.*.Title' => 'required|string|in:Mr,Mrs,Ms,Miss',
            'Passenger.*.FirstName' => 'required|string|max:50',
            'Passenger.*.LastName' => 'required|string|max:50',
            'Passenger.*.BeneficiaryTitle' => 'required|string|in:Mr,Mrs,Ms,Miss',
            'Passenger.*.BeneficiaryName' => 'required|string',
            'Passenger.*.RelationShipToInsured' => 'required|string',
            'Passenger.*.RelationToBeneficiary' => 'required|string',
            'Passenger.*.Gender' => 'required|string|in:1,2',
            'Passenger.*.DOB' => 'required|date_format:Y-m-d|before:today',
            'Passenger.*.PassportNo' => 'required|string',
            'Passenger.*.PhoneNumber' => 'required|string|regex:/^\d{10}$/',
            'Passenger.*.EmailId' => 'required|email',
            'Passenger.*.AddressLine1' => 'required|string',
            'Passenger.*.CityCode' => 'required|string|size:3',
            'Passenger.*.CountryCode' => 'required|string|in:IND',
            'Passenger.*.MajorDestination' => 'required|string|in:INDIA',
            'Passenger.*.PassportCountry' => 'required|string|size:2',
            'Passenger.*.PinCode' => 'required|string|regex:/^\d{6}$/',
            'EndUserIp' => 'nullable|ip',
            'GenerateInsurancePolicy' => 'nullable|string|in:true,false',
        ]);

        // Transform payload for API compatibility
        $requestData = [
            'EndUserIp' => $validatedData['EndUserIp'] ?? $request->ip(),
            'TokenId' => $token,
            'TraceId' => $validatedData['TraceId'],
            'ResultIndex' => $validatedData['ResultIndex'],
            'GenerateInsurancePolicy' => $validatedData['GenerateInsurancePolicy'] ?? 'false',
            'Passenger' => array_map(function ($passenger) {
                $passenger['PassportNo'] = strtoupper($passenger['PassportNo']);
                $passenger['BeneficiaryTitle'] = strtoupper($passenger['BeneficiaryTitle']);
                return $passenger;
            }, $validatedData['Passenger']),
        ];

        // Log request data with masked sensitive fields
        $safeRequestData = $requestData;
        foreach ($safeRequestData['Passenger'] as &$passenger) {
            $passenger['PassportNo'] = '[MASKED]';
            $passenger['EmailId'] = '[MASKED]';
            $passenger['PhoneNumber'] = '[MASKED]';
        }
        Log::info('Insurance booking request', [
            'data' => $safeRequestData,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Call API
        $apiUrl = 'https://booking.travelboutiqueonline.com/InsuranceAPI_V1/InsuranceService.svc/rest/Book';
        $maxRetries = 1;
        $retryCount = 0;

        while ($retryCount <= $maxRetries) {
            $response = Http::timeout(90)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Laravel Client',
                    'Accept' => 'application/json',
                ])
                ->post($apiUrl, $requestData);

            if ($response->failed()) {
                Log::error('Insurance booking API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'API request failed',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ], $response->status());
            }

            $responseData = $response->json();
            if (is_null($responseData)) {
                Log::error('Invalid or empty response from insurance booking API', [
                    'body' => $response->body(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or empty response from API',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ], 500);
            }

            $errorCode = data_get($responseData, 'Response.Error.ErrorCode');
            $errorMessage = data_get($responseData, 'Response.Error.ErrorMessage');

            if ($errorCode === 6 || $errorCode === 401) {
                Log::warning('Token error in insurance booking, retrying with new token', [
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'timestamp' => now()->toDateTimeString(),
                ]);
                $token = $this->apiService->authenticate();
                if (!$token) {
                    Log::error('Failed to retrieve new authentication token for booking', [
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to retrieve new authentication token',
                    ], 500);
                }
                $requestData['TokenId'] = $token;
                $retryCount++;
                continue;
            }

            if ($errorCode !== 0) {
                Log::error('Insurance booking failed', [
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'request' => $safeRequestData,
                    'response_body' => $response->body(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage ?? 'Insurance booking failed',
                    'error_code' => $errorCode,
                    'response' => $responseData,
                ], 400);
            }

            // Prepare email and PDF content dynamically
            $pax = data_get($responseData, 'Response.Itinerary.PaxInfo.0');
            $itinerary = data_get($responseData, 'Response.Itinerary');
            $coverageDetails = data_get($responseData, 'Response.Itinerary.CoverageDetails', []);

            // Format dates
            $startDate = \Carbon\Carbon::parse($itinerary['PolicyStartDate'])->format('F j, Y');
            $endDate = \Carbon\Carbon::parse($itinerary['PolicyEndDate'])->format('F j, Y');
            $bookingDate = \Carbon\Carbon::parse($itinerary['CreatedOn'])->format('F j, Y, g:i A T');
            $dob = \Carbon\Carbon::parse($pax['DOB'])->format('F j, Y');

            // Generate coverage list
            $coverageList = array_map(function ($coverage) {
                return "<li>{$coverage['Coverage']}: INR {$coverage['SumInsured']}</li>";
            }, $coverageDetails);
            $coverageHtml = implode('', $coverageList);

            // Prepare booking details for email and PDF
            $bookingDetails = [
                'pax' => $pax,
                'itinerary' => $itinerary,
                'coverageHtml' => $coverageHtml,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'bookingDate' => $bookingDate,
                'dob' => $dob,
                'recipientEmail' => $pax['EmailId'],
            ];

            // Save to database
            $passengerData = $validatedData['Passenger'][0]; // Assuming single passenger for simplicity
            $insuranceRecord = BookInsurace::create([
                'booking_id' => $itinerary['BookingId'],
                'trace_id' => $validatedData['TraceId'],
                'result_index' => $validatedData['ResultIndex'],
                'title' => $passengerData['Title'],
                'first_name' => $passengerData['FirstName'],
                'last_name' => $passengerData['LastName'],
                'beneficiary_title' => $passengerData['BeneficiaryTitle'],
                'beneficiary_name' => $passengerData['BeneficiaryName'],
                'relationship_to_insured' => $passengerData['RelationShipToInsured'],
                'relationship_to_beneficiary' => $passengerData['RelationToBeneficiary'],
                'gender' => $passengerData['Gender'],
                'dob' => $passengerData['DOB'],
                'passport_no' => strtoupper($passengerData['PassportNo']),
                'phone_number' => $passengerData['PhoneNumber'],
                'email' => $passengerData['EmailId'],
                'address_line1' => $passengerData['AddressLine1'],
                'city_code' => $passengerData['CityCode'],
                'country_code' => $passengerData['CountryCode'],
                'major_destination' => $passengerData['MajorDestination'],
                'passport_country' => $passengerData['PassportCountry'],
                'pin_code' => $passengerData['PinCode'],
                'policy_start_date' => $itinerary['PolicyStartDate'],
                'policy_end_date' => $itinerary['PolicyEndDate'],
                'coverage_details' => json_encode($coverageDetails),
                'status' => 'confirmed',
            ]);

            // Log database storage
            Log::info('Insurance booking saved to database', [
                'booking_id' => $itinerary['BookingId'],
                'record_id' => $insuranceRecord->id,
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Generate PDF
            $pdfFileName = 'insurance_booking_' . $itinerary['BookingId'] . '_' . time() . '.pdf';
            $pdfPath = 'public/pdfs/' . $pdfFileName;
            $pdf = Pdf::loadView('pdf.insurance_booking_confirmation', $bookingDetails);
            Storage::put($pdfPath, $pdf->output());
            $pdfUrl = Storage::url($pdfPath);

            // Update record with PDF URL
            $insuranceRecord->update(['pdf_url' => $pdfUrl]);

            // Log PDF generation
            Log::info('PDF generated for insurance booking', [
                'booking_id' => $itinerary['BookingId'],
                'pdf_path' => $pdfPath,
                'pdf_url' => $pdfUrl,
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Send email with PDF attachment
            try {
                Mail::to($bookingDetails['recipientEmail'])->send(new InsuranceBookingConfirmation($bookingDetails, Storage::path($pdfPath)));
                Log::info('Insurance booking confirmation email sent successfully', [
                    'email' => $bookingDetails['recipientEmail'],
                    'booking_id' => $itinerary['BookingId'],
                    'pdf_path' => $pdfPath,
                    'timestamp' => now()->toDateTimeString(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send insurance booking confirmation email', [
                    'email' => $bookingDetails['recipientEmail'],
                    'booking_id' => $itinerary['BookingId'],
                    'pdf_path' => $pdfPath,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
                // Continue with response even if email fails
            }

            // Log successful response
            Log::info('Insurance booking successful', [
                'response' => $responseData,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'pdf_url' => $pdfUrl,
                'record_id' => $insuranceRecord->id,
            ], 200);
        }

        // Max retries exceeded
        Log::error('Max retries exceeded for insurance booking', [
            'timestamp' => now()->toDateTimeString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Max retries exceeded for insurance booking',
        ], 500);

    } catch (ValidationException $e) {
        Log::error('Validation failed for insurance booking', [
            'errors' => $e->errors(),
            'request' => $request->all(),
            'timestamp' => now()->toDateTimeString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (RequestException $e) {
        Log::error('HTTP request failed for insurance booking', [
            'error' => $e->getMessage(),
            'request' => $request->all(),
            'timestamp' => now()->toDateTimeString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to communicate with the insurance API',
            'error' => $e->getMessage(),
        ], 500);
    } catch (\Throwable $e) {
        Log::error('Unexpected error in insurance booking', [
            'error' => $e->getMessage(),
            'request' => $request->all(),
            'timestamp' => now()->toDateTimeString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
}

 



    public function generatePolicy(Request $request)
    {
        try {
     
            $token = $this->apiService->getToken();
            if (!$token) {
                Log::error('Failed to retrieve authentication token for policy generation');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve authentication token',
                ], 500);
            }

            // Validate request
            $validated = $request->validate([
                'EndUserIp' => 'required|ip',
                'BookingId' => 'required|integer',
            ]);

            // Add TokenId to request
            $validated['TokenId'] = $token;

            // Log request data
            Log::info('Insurance policy generation request', ['data' => $validated]);

            // Call API
            $apiUrl = 'https://booking.travelboutiqueonline.com/InsuranceAPI_V1/InsuranceService.svc/rest/GeneratePolicy';
            $response = Http::timeout(90)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($apiUrl, $validated);

            // Check for HTTP errors
            if ($response->failed()) {
                Log::error('Insurance policy generation API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'API request failed while generating policy',
                    'error' => $response->body(),
                ], $response->status());
            }

            // Parse response
            $responseData = $response->json();
            if (is_null($responseData)) {
                Log::error('Invalid or empty response from policy generation API', [
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or empty response from API',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ], 500);
            }

            // Check for API error
            $errorCode = data_get($responseData, 'Response.Error.ErrorCode');
            $errorMessage = data_get($responseData, 'Response.Error.ErrorMessage');

            if ($errorCode !== 0) {
                Log::error('Insurance policy generation failed', [
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'response' => $responseData,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage ?? 'Policy generation failed',
                    'error_code' => $errorCode,
                    'response' => $responseData,
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $responseData,
            ], 200);

        } catch (ValidationException $e) {
            Log::error('Validation failed for policy generation', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (RequestException $e) {
            Log::error('HTTP request failed for policy generation', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to communicate with the insurance API',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Unexpected error in policy generation', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the policy',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


      public function testEmail(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'email' => 'required|email',
            ]);

            // Dummy API response (based on provided booking response)
            $dummyResponse = [
                'Response' => [
                    'ResponseStatus' => 1,
                    'Error' => [
                        'ErrorCode' => 0,
                        'ErrorMessage' => '',
                    ],
                    'TraceId' => '0c883f04-d5e5-4a39-97a6-89184ef45897',
                    'Itinerary' => [
                        'BookingId' => 1982706,
                        'InsuranceId' => 24654,
                        'PlanType' => 1,
                        'PlanName' => 'SANKASH 100D 30 DAYS (additional 10% cashback)',
                        'PlanDescription' => null,
                        'PlanCoverage' => 4,
                        'CoverageDetails' => [
                            ['Coverage' => 'Hospitalization expenses for Injury', 'SumInsured' => '100000', 'Excess' => null],
                            ['Coverage' => 'Outpatient Treatment Expenses for Injury', 'SumInsured' => '50000', 'Excess' => null],
                            ['Coverage' => 'Medical Evacuation', 'SumInsured' => '50000', 'Excess' => null],
                            ['Coverage' => 'Personal Accident', 'SumInsured' => '400000', 'Excess' => null],
                            ['Coverage' => 'Trip Cancellation', 'SumInsured' => '10000', 'Excess' => null],
                            ['Coverage' => 'Trip Interruption & Curtailment', 'SumInsured' => '10000', 'Excess' => null],
                            ['Coverage' => 'Repatriation of remains', 'SumInsured' => '50000', 'Excess' => null],
                            ['Coverage' => 'Total Loss of Checked-in Baggage', 'SumInsured' => '4000', 'Excess' => null],
                            ['Coverage' => 'Delay of Checked-in Baggage', 'SumInsured' => '2000', 'Excess' => null],
                        ],
                        'PlanCategory' => 1,
                        'PaxInfo' => [
                            [
                                'PaxId' => 29495,
                                'PolicyNo' => '',
                                'ClaimCode' => null,
                                'SiebelPolicyNumber' => '',
                                'ReferenceId' => 'TBO-18052025171444030',
                                'DocumentURL' => null,
                                'MaxAge' => 70,
                                'MinAge' => 0,
                                'Title' => 'Mr',
                                'FirstName' => 'manshu',
                                'LastName' => 'mehra',
                                'Gender' => 'Male',
                                'DOB' => '1990-07-17T00:00:00',
                                'BeneficiaryName' => 'Mr manshu mehra',
                                'RelationShipToInsured' => 'Self',
                                'RelationToBeneficiary' => 'Spouse',
                                'EmailId' => $validated['email'],
                                'PhoneNumber' => '7988532993',
                                'PassportNo' => 'AANA1234',
                                'AddressLine1' => 'Ambala',
                                'AddressLine2' => null,
                                'Country' => 'India',
                                'State' => 'IXC',
                                'City' => 'IXC',
                                'PinCode' => '133001',
                                'MajorDestination' => 'INDIA',
                                'Price' => [
                                    'Currency' => 'INR',
                                    'GrossFare' => 207,
                                    'PublishedPrice' => 207,
                                    'PublishedPriceRoundedOff' => 207,
                                    'OfferedPrice' => 207,
                                    'OfferedPriceRoundedOff' => 207,
                                    'CommissionEarned' => 0,
                                    'TdsOnCommission' => 0,
                                    'ServiceTax' => 0,
                                    'SwachhBharatTax' => 0,
                                    'KrishiKalyanTax' => 0,
                                ],
                                'OldPolicyNumber' => '',
                                'PolicyStatus' => 2,
                                'ErrorMsg' => '',
                            ],
                        ],
                        'PolicyStartDate' => '2025-06-01T00:00:00',
                        'PolicyEndDate' => '2025-06-30T00:00:00',
                        'CreatedOn' => '2025-05-18T17:14:44',
                        'Source' => 'Sankash',
                        'IsDomestic' => true,
                        'Status' => 2,
                        'BookingHistory' => [
                            [
                                'CreatedBy' => 58147,
                                'CreatedByName' => 'Vishal Rana',
                                'CreatedOn' => '2025-05-18T17:14:44',
                                'EventCategory' => 7,
                                'LastModifiedBy' => 58147,
                                'LastModifiedByName' => 'Vishal Rana',
                                'LastModifiedOn' => '2025-05-18T17:14:44',
                                'Remarks' => 'Booking is Ready (Booked By BookingAPI). Insurance details saved for PlanCode : 8 | Source : Sankash (through New BookingEngine Service). | IP Address :- 223.178.209.53 | MSDTC : OFF',
                            ],
                        ],
                        'GSTIN' => null,
                        'SupplierName' => 'SANKASH',
                    ],
                ],
            ];

            // Prepare email and PDF content dynamically
            $pax = data_get($dummyResponse, 'Response.Itinerary.PaxInfo.0');
            $itinerary = data_get($dummyResponse, 'Response.Itinerary');
            $coverageDetails = data_get($dummyResponse, 'Response.Itinerary.CoverageDetails', []);

            // Format dates
            $startDate = \Carbon\Carbon::parse($itinerary['PolicyStartDate'])->format('F j, Y');
            $endDate = \Carbon\Carbon::parse($itinerary['PolicyEndDate'])->format('F j, Y');
            $bookingDate = \Carbon\Carbon::parse($itinerary['CreatedOn'])->format('F j, Y, g:i A T');
            $dob = \Carbon\Carbon::parse($pax['DOB'])->format('F j, Y');

            // Generate coverage list
            $coverageList = array_map(function ($coverage) {
                return "<li>{$coverage['Coverage']}: INR {$coverage['SumInsured']}</li>";
            }, $coverageDetails);
            $coverageHtml = implode('', $coverageList);

            // Prepare booking details for email and PDF
            $bookingDetails = [
                'pax' => $pax,
                'itinerary' => $itinerary,
                'coverageHtml' => $coverageHtml,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'bookingDate' => $bookingDate,
                'dob' => $dob,
                'recipientEmail' => $pax['EmailId'],
            ];

            // Generate PDF
            $pdfFileName = 'insurance_booking_' . $itinerary['BookingId'] . '_' . time() . '.pdf';
            $pdfPath = 'public/pdfs/' . $pdfFileName;
            $pdf = Pdf::loadView('pdf.insurance_booking_confirmation', $bookingDetails);
            Storage::put($pdfPath, $pdf->output());
            $pdfUrl = Storage::url($pdfPath);

            // Log PDF generation
            Log::info('PDF generated for test email', [
                'booking_id' => $itinerary['BookingId'],
                'pdf_path' => $pdfPath,
                'pdf_url' => $pdfUrl,
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Send email with PDF attachment
            try {
                Mail::to($bookingDetails['recipientEmail'])->send(new InsuranceBookingConfirmation($bookingDetails, Storage::path($pdfPath)));
                Log::info('Test email with PDF attachment sent successfully', [
                    'email' => $bookingDetails['recipientEmail'],
                    'booking_id' => $itinerary['BookingId'],
                    'pdf_path' => $pdfPath,
                    'timestamp' => now()->toDateTimeString(),
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Test email with PDF attachment sent successfully',
                    'email' => $bookingDetails['recipientEmail'],
                    'pdf_url' => $pdfUrl,
                ], 200);
            } catch (\Exception $e) {
                Log::error('Failed to send test email with PDF attachment', [
                    'email' => $bookingDetails['recipientEmail'],
                    'booking_id' => $itinerary['BookingId'],
                    'pdf_path' => $pdfPath,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test email with PDF attachment',
                    'error' => $e->getMessage(),
                    'pdf_url' => $pdfUrl,
                ], 500);
            }
        } catch (ValidationException $e) {
            Log::error('Validation failed for test email', [
                'errors' => $e->errors(),
                'request' => $request->all(),
                'timestamp' => now()->toDateTimeString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Unexpected error in test email', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'timestamp' => now()->toDateTimeString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
        
       




  
        public function getBookingDetail(Request $request)
        {
            try {
                // Get authentication token
                $token = $this->apiService->getToken();
                if (!$token) {
                    Log::error('Failed to retrieve authentication token for booking details');
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to retrieve authentication token',
                    ], 500);
                }

                // Validate request
                $validated = $request->validate([
                    'EndUserIp' => 'required|ip',
                    'BookingId' => 'required|integer',
                ]);

                // Add TokenId to request
                $validated['TokenId'] = $token;

                // Log request data
                Log::info('Insurance booking details request', ['data' => $validated]);

                // Call API
                $apiUrl = 'https://booking.travelboutiqueonline.com/InsuranceAPI_V1/InsuranceService.svc/rest/GetBookingDetail';
                $response = Http::timeout(90)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                    ->post($apiUrl, $validated);

                // Check for HTTP errors
                if ($response->failed()) {
                    Log::error('Insurance booking details API request failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'API request failed while fetching booking details',
                        'error' => $response->body(),
                ], $response->status());
            }

            // Parse response
            $responseData = $response->json();
            if (is_null($responseData) || !isset($responseData['Response']) || empty($responseData['Response'])) {
                Log::error('Invalid or empty response from booking details API', [
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or empty response from API',
                    'error' => $responseData,
                ], 500);
            }

            // Check for API error
            $errorCode = data_get($responseData, 'Response.Error.ErrorCode');
            $errorMessage = data_get($responseData, 'Response.Error.ErrorMessage');

            if ($errorCode !== 0) {
                Log::error('Insurance booking details failed', [
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'response' => $responseData,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage ?? 'Failed to fetch booking details',
                    'error_code' => $errorCode,
                    'response' => $responseData,
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $responseData,
            ], 200);

        } catch (ValidationException $e) {
            Log::error('Validation failed for booking details', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (RequestException $e) {
            Log::error('HTTP request failed for booking details', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to communicate with the insurance API',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Unexpected error in booking details', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching booking details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}