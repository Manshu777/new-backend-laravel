<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Facades\Http;
   use Illuminate\Support\Facades\Log;

class InsuranceController extends Controller
{
    //
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }





    public function GetInsurance(Request $request){
        $token = $this->apiService->getToken();

        $validate=$request->validate([
     'EndUserIp' => 'required|ip',
      'PlanCategory' => 'required|in:1,2,3,4,5,6',
      'PlanCoverage' => 'required|in:1,2,3,4,5,6,7,8',
      'PlanType' => 'required|in:1,2',
   
          'TravelStartDate' => 'required',

      'TravelEndDate' => 'required',
      'NoOfPax' => 'required|integer|min:1',
      'PaxAge' => 'required|array',
     ]);


     
     $validate["TokenId"]=$token;
   

     $response= Http::timeout(100)->post("https://api.travelboutiqueonline.com/InsuranceAPI_V1/InsuranceService.svc/rest/Search",$validate);

if($response->json('Response.Error.ErrorCode') === 6){

    $token = $this->apiService->authenticate();
    $validate['TokenId'] = $token;
    $response= Http::timeout(100)->post("https://api.travelboutiqueonline.com/InsuranceAPI_V1/InsuranceService.svc/rest/Search",$validate);
}
  


return $response ;



    } 


 public function searchInsurance(Request $request)
    {
        try {
            // Validate request parameters
         $validated = $request->validate([
    'EndUserIp'       => 'required|ip',
    'PlanCategory'    => 'required|integer|in:1,2,3,4,5,6',
    'PlanCoverage'    => 'required|integer|in:1,2,3,4,5,6,7,8',
    'PlanType'        => 'required|integer|in:1,2',
    'TravelStartDate' => 'required|date_format:Y-m-d',
    'TravelEndDate'   => 'required|date_format:Y-m-d|after_or_equal:TravelStartDate',
    'NoOfPax'         => 'required|integer|min:1',
    'PaxAge'          => 'required|array|min:1',
    'PaxAge.*'        => 'required|integer|min:1|max:100',
]);
            // Get token from .env
            $token = config('services.insurance.token');
            if (empty($token)) {
                Log::error('Insurance API token is not configured');
                return response()->json([
                    'success' => false,
                    'message' => 'Insurance API token is not configured',
                ], 500);
            }

            // Prepare payload, converting dates to ISO 8601
            $payload = [
                'TokenID' => $token,
                'EndUserIp' => $validated['EndUserIp'],
                'PlanCategory' => $validated['PlanCategory'],
                'PlanCoverage' => $validated['PlanCoverage'],
                'PlanType' => $validated['PlanType'],
                'TravelStartDate' => $validated['TravelStartDate'] . 'T00:00:00',
                'TravelEndDate' => $validated['TravelEndDate'] . 'T00:00:00',
                'NoOfPax' => $validated['NoOfPax'],
                'PaxAge' => $validated['PaxAge'],
            ];

            // Log request payload
            Log::info('Insurance API Request Payload', ['payload' => $payload]);

            // Define API endpoint
            $apiUrl = 'https://api.travelboutiqueonline.com/InsuranceAPI_V1/InsuranceService.svc/rest/Search';

            // Send request with headers
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($apiUrl, $payload);

            // Log response
            Log::info('Insurance API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Parse response
            $responseData = $response->json();

            // Check for API errors
            if ($response->failed() || !empty($responseData['Error']['ErrorCode'])) {
                $errorCode = $responseData['Error']['ErrorCode'] ?? 0;
                $errorMessage = $responseData['Error']['ErrorMessage'] ?? 'Unknown error';

                Log::error('Insurance API Error', [
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'trace_id' => $responseData['TraceId'] ?? 'N/A',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error_code' => $errorCode,
                    'trace_id' => $responseData['TraceId'] ?? null,
                ], 400);
            }

            // Return success response
            return response()->json([
                'success' => true,
                'data' => $responseData['Results'] ?? [],
                'trace_id' => $responseData['TraceId'] ?? null,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP Client Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to communicate with the insurance API',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
}


 




    

public function bookInsurance(Request $request)
{
    try {
        // Get authentication token
        $token = $this->apiService->getToken();
        if (!$token) {
            throw new \Exception('Failed to retrieve authentication token');
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
            'Passenger.*.Gender' => 'required',
            'Passenger.*.DOB' => 'required',
            'Passenger.*.PassportNo' => 'required|string',
            'Passenger.*.PhoneNumber' => 'required|string',
            'Passenger.*.EmailId' => 'required|email',
            'Passenger.*.AddressLine1' => 'required|string',
      
            'Passenger.*.CityCode' => 'required|string',
            'Passenger.*.CountryCode' => 'required|string',
            'Passenger.*.MajorDestination' => 'required|string',
            'Passenger.*.PassportCountry' => 'required|string',
            'Passenger.*.PinCode' => 'required',
            'EndUserIp' => 'nullable|ip',
            'GenerateInsurancePolicy' => 'nullable|string|in:true,false',
        ]);

        // Build request data
        $requestData = [
            'EndUserIp' => $validatedData['EndUserIp'],
            'TokenId' => $token,
            'TraceId' => $validatedData['TraceId'],
            'ResultIndex' => $validatedData['ResultIndex'],
            'GenerateInsurancePolicy' => $validatedData['GenerateInsurancePolicy'] ?? 'false',
            'Passenger' => array_map(function ($passenger) {
                // Normalize sensitive fields
                $passenger['PassportNo'] = strtoupper($passenger['PassportNo']);
                $passenger['BeneficiaryTitle'] = strtoupper($passenger['BeneficiaryTitle'] ?? '');
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
       

        // Call API
        $apiUrl = 'https://InsuranceBE.tektravels.com/InsuranceService.svc/rest/book';
        $maxRetries = 1;
        $retryCount = 0;
        $response = null;
        $responseData = null;

        while ($retryCount <= $maxRetries) {
            $response = Http::timeout(90)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Laravel Client',
                    'Accept' => 'application/json',
                ])
                ->post($apiUrl, $requestData);

          
            // Check for HTTP errors
            if ($response->failed() || $response->status() !== 200) {
            
                return response()->json([
                    'success' => false,
                    'message' => 'API request failed',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], $response->status());
            }

            // Check if response is valid JSON
            try {
                $responseData = $response->json();
                if (is_null($responseData)) {
                  
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or empty response from API',
                        'status' => $response->status(),
                        'body' => $response->body()
                    ], 500);
                }
            } catch (\Exception $e) {
              
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or empty response from API',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], 500);
            }

            // Check for API error
            $errorCode = data_get($responseData, 'Response.Error.ErrorCode');
            $errorMessage = data_get($responseData, 'Response.Error.ErrorMessage');

            if ($errorCode === 6 || $errorCode === 401) {
                // Retry with new token
                $token = $this->apiService->authenticate();
                if (!$token) {
                    throw new \Exception('Failed to retrieve new authentication token');
                }
              
                $requestData['TokenId'] = $token;
                $retryCount++;
                continue;
            }

            break;
        }

        if ($errorCode !== 0) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage ?? 'Insurance booking failed',
                'error_code' => $errorCode,
                'response' => $responseData
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation Error',
            'errors' => $e->errors(),
            'code' => $e->getCode()
        ], 422);
    } catch (\Illuminate\Http\Client\RequestException $e) {
       
        return response()->json([
            'success' => false,
            'message' => 'HTTP request failed',
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ], 500);
    } catch (\Throwable $e) {
 
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ], 500);
    }
}




public function generatePolicy(Request $request)
{
    try {
        // Get API Token
        $token = $this->apiService->getToken();

        // Validate request data
        $validated = $request->validate([
            'EndUserIp' => 'required|ip',
            'BookingId' => 'required|integer',
        ]);

        // Add TokenId to the request
        $validated["TokenId"] = $token;

        // API URL
        $apiUrl = "https://InsuranceBE.tektravels.com/InsuranceService.svc/rest/GeneratePolicy";

        // Send request to API
        $response = Http::post($apiUrl, $validated);

        // Check if API response failed
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'API request failed while generating policy.',
                'error'   => $response->body(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'data'    => $response->json(),
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Illuminate\Http\Client\RequestException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to communicate with the insurance API',
            'error'   => $e->getMessage(),
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred while generating the policy.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

public function getBookingDetail(Request $request)
{
    try {
        // Generate API Token
        $validated = $request->validate([
            'EndUserIp' => 'required|ip',
            'BookingId' => 'required|integer',
        ]);

        // Add the generated TokenId to the request
        $validated["TokenId"] = $this->apiService->getToken();

        // API URL
        $apiUrl = "https://InsuranceBE.tektravels.com/InsuranceService.svc/rest/GetBookingDetail";

        // Send request to TekTravels API
        $response = Http::post($apiUrl, $validated);

        // Check if API response failed
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'API request failed while fetching booking details.',
                'error'   => $response->body(),
            ], $response->status());
        }

        // Decode API response
        $apiResponse = $response->json();

        // Check if the response contains valid data
        if (!isset($apiResponse['Response']) || empty($apiResponse['Response'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid response received from the API.',
                'error'   => $apiResponse,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data'    => $apiResponse,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Illuminate\Http\Client\RequestException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to communicate with the insurance API.',
            'error'   => $e->getMessage(),
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred while fetching booking details.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



}