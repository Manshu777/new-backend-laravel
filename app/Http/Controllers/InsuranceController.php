<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
                Log::error('Failed to retrieve authentication token for booking');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve authentication token',
                ], 500);
            }

            // Validate request
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
                'Passenger.*.Gender' => 'required|string',
                'Passenger.*.DOB' => 'required|date_format:Y-m-d',
                'Passenger.*.PassportNo' => 'required|string',
                'Passenger.*.PhoneNumber' => 'required|string',
                'Passenger.*.EmailId' => 'required|email',
                'Passenger.*.AddressLine1' => 'required|string',
                'Passenger.*.CityCode' => 'required|string',
                'Passenger.*.CountryCode' => 'required|string',
                'Passenger.*.MajorDestination' => 'required|string',
                'Passenger.*.PassportCountry' => 'required|string',
                'Passenger.*.PinCode' => 'required|string',
                'EndUserIp' => 'nullable|ip',
                'GenerateInsurancePolicy' => 'nullable|string|in:true,false',
            ]);

            // Build request data
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
            Log::info('Insurance booking request', ['data' => $safeRequestData]);

            // Call API
            $apiUrl = 'https://booking.travelboutiqueonline.com/InsuranceAPI_V1/InsuranceService.svc/rest/book';
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

                // Check for HTTP errors
                if ($response->failed()) {
                    Log::error('Insurance booking API request failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'API request failed',
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ], $response->status());
                }

                // Parse response
                $responseData = $response->json();
                if (is_null($responseData)) {
                    Log::error('Invalid or empty response from insurance booking API', [
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

                if ($errorCode === 6 || $errorCode === 401) {
                    Log::warning('Token error in insurance booking, retrying with new token', [
                        'errorCode' => $errorCode,
                        'errorMessage' => $errorMessage,
                    ]);
                    $token = $this->apiService->authenticate();
                    if (!$token) {
                        Log::error('Failed to retrieve new authentication token for booking');
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
                        'response' => $responseData,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage ?? 'Insurance booking failed',
                        'error_code' => $errorCode,
                        'response' => $responseData,
                    ], 400);
                }

                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                ], 200);
            }

            // Max retries exceeded
            Log::error('Max retries exceeded for insurance booking');
            return response()->json([
                'success' => false,
                'message' => 'Max retries exceeded for insurance booking',
            ], 500);

        } catch (ValidationException $e) {
            Log::error('Validation failed for insurance booking', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (RequestException $e) {
            Log::error('HTTP request failed for insurance booking', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to communicate with the insurance API',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Unexpected error in insurance booking', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate an insurance policy.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generatePolicy(Request $request)
    {
        try {
            // Get authentication token
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

    /**
     * Retrieve booking details for an insurance policy.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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