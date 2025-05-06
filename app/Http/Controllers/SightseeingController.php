<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ApiService;


class SightseeingController extends Controller
{

    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }


    public function search(Request $request)
    {
        try {
            // Validate incoming request data
            $request->validate([
                'CountryCode' => 'required|string',
                'TravelStartDate' => 'required|date',
                'FromDate' => 'required|date',
                'ToDate' => 'required|date',
                'AdultCount' => 'required|integer',
                'ChildCount' => 'nullable|integer',
                'ChildAge' => 'nullable|array',
                'PreferredLanguage' => 'nullable|integer',
                'PreferredCurrency' => 'required|string',
                'IsBaseCurrencyRequired' => 'required|boolean',
                'EndUserIp' => 'required|ip',
                
                'BookingMode' => 'required|integer',
                'KeyWord' => 'nullable|string',
            ]);
    
            // Fetch data and obtain a valid token
            $data = $request->all();
            $token = $this->apiService->getToken();
            $data['TokenId'] = $token; 
    
            // Call the external API
            $response = Http::timeout(90) // Timeout handling
                ->post('http://sightseeingbe.tektravels.com/internalSightseeingService.svc/rest/Search', $data);
    
            // Check for successful response
            if ($response->successful()) {
                return response()->json($response->json(), 200);
            }
    
            // Token expiration error handling (ErrorCode = 6)
            if ($response->json('Response.Error.ErrorCode') === 6) {
                $token = $this->apiService->authenticate();
                $data['TokenId'] = $token; // Update the token
    
                // Retry the request with the new token
                $response = Http::timeout(90)->post('http://sightseeingbe.tektravels.com/internalSightseeingService.svc/rest/Search', $data);
    
                // Check if the retried request is successful
                if ($response->successful()) {
                    return response()->json($response->json(), 200);
                }
            }
    
            // If still not successful, return an error message
            return response()->json(['error' => 'Failed to retrieve data', 'details' => $response->json()], $response->status());
    
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Handle client request exceptions (e.g., connection issues, timeouts)
            return response()->json(['error' => 'Request failed', 'message' => $e->getMessage()], 500);
    
        } catch (\Exception $e) {
            // Handle any other general exceptions
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function meRandomdata(Request $request)
    {
        try {
            // Validate incoming request data
            $request->validate([
                'CountryCodes' => 'required|array', // Expecting an array of country codes
                'CountryCodes.*' => 'required|string', // Each country code should be a string
                'TravelStartDate' => 'required|date',
                'FromDate' => 'required|date',
                'ToDate' => 'required|date',
                'AdultCount' => 'required|integer',
                'ChildCount' => 'nullable|integer',
                'ChildAge' => 'nullable|array',
                'PreferredLanguage' => 'nullable|integer',
                'PreferredCurrency' => 'required|string',
                'IsBaseCurrencyRequired' => 'required|boolean',
                'EndUserIp' => 'required|ip',
                'BookingMode' => 'required|integer',
                'KeyWord' => 'nullable|string',
            ]);
    
            $countryCodes = $request->input('CountryCodes', []);
    
            if (empty($countryCodes)) {
                return response()->json(['error' => 'CountryCodes array is required and cannot be empty'], 400);
            }
    
            $allResponses = [];
    
            foreach ($countryCodes as $countryCode) {
                // Fetch random cities data for the current country code
                $randomCitiesData = $this->getMultipleRandomCityData($countryCode, 3);
    
                // Ensure random cities data is not empty
                if (empty($randomCitiesData)) {
                    $allResponses[] = [
                        'error' => "No cities found for country: $countryCode",
                    ];
                    continue; // Skip to the next iteration
                }
    
                // Prepare the data for the API request
                $data = array_merge($request->all(), ['Cities' => $randomCitiesData]);
    
                // Fetch a valid token
                $token = $this->apiService->getToken();
                $data['TokenId'] = $token;
    
                // Call the external API with the merged data
                $response = Http::timeout(120)->post('http://sightseeingbe.tektravels.com/internalSightseeingService.svc/rest/Search', $data);
    
                if ($response->successful()) {
                    $allResponses[] = $response->json();
                } else {
                    $this->handleApiError($response, $countryCode, $data, $allResponses);
                }
            }
    
            return response()->json($allResponses, 200);
    
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return response()->json(['error' => 'Request failed', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
    
    
   
    protected function handleApiError($response, $countryCode, $data, &$allResponses)
    {
     
     
        try {
            // Re-authenticate to get a fresh token
            $token = $this->apiService->authenticate(); 
            $data['TokenId'] = $token;
        
            // Retry the request with the new token
            $retryResponse = Http::timeout(120)->post('http://sightseeingbe.tektravels.com/internalSightseeingService.svc/rest/Search', $data);
        
            // Check if the retry response was successful
            if ($retryResponse->successful()) {
                $allResponses[] = $retryResponse->json(); 
            } else {
                // Log or record the failure to retrieve data for the country
                $allResponses[] = [
                    'error' => 'Failed to retrieve data for country: ' . $countryCode,
                    'details' => $retryResponse->json()
                ];
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Handle specific request-related exceptions
            throw new \Exception("Invalid country code: $countryCode");
            $allResponses[] = [
                'error' => 'Request failed during retry for country: ' . $countryCode,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {

            throw new \Exception("Invalid country code: $countryCode");
            $allResponses[] = [
                'error' => 'An unexpected error occurred during retry for country: ' . $countryCode,
                'message' => $e->getMessage()
            ];
        }
        
        
    }
    
    
    
    public function getMultipleRandomCityData($countryCode, $count = 3)
    {
        try {
            $cities = [
                'US' => [
                    ['CityId' => '125099', 'CountryCode' => 'US'],
                ],
                'AE' => [
                    ['CityId' => '115936', 'CountryCode' => 'AE'],
                ],
                'IN' => [
                    ['CityId' => '144306', 'CountryCode' => 'IN'],
                    ['CityId' => '138673', 'CountryCode' => 'IN'],
                ],
            ];
    
            if (!isset($cities[$countryCode])) {
                throw new \Exception("Invalid country code: $countryCode");
            }
    
            $randomCitiesData = [];
    
            // Check the actual number of available cities for this country
            $availableCities = count($cities[$countryCode]);
    
            // Fetch up to the specified number of random cities, limited by availability
            for ($i = 0; $i < min($count, $availableCities); $i++) {
                $randomCity = $cities[$countryCode][array_rand($cities[$countryCode])];
    
                $randomCitiesData[] = array_merge($randomCity, [
                    "TravelStartDate" => "2024-10-15T00:00:00",
                    "FromDate" => "2024-10-18T00:00:00",
                    "ToDate" => "2024-10-20T00:00:00",
                    'AdultCount' => 1,
                    'ChildCount' => 0,
                    'ChildAge' => null,
                    'PreferredLanguage' => 0,
                    'PreferredCurrency' => 'INR',
                    'IsBaseCurrencyRequired' => false,
                    'EndUserIp' => "223.178.208.152",
                    'KeyWord' => '',
                ]);
            }
    
            // Check if we fetched any cities, otherwise return null
            if (empty($randomCitiesData)) {
                throw new \Exception("No city data found for country: $countryCode");
            }
    
            return $randomCitiesData;
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve city data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    

    
    
    public function GetSingleActivity(Request $request)
    {
   $validatetion=$request->validate([
    "ResultIndex"=>"required",
    "TraceId"=>"required",
    "EndUserIp"=>"required"
   ]);
   $token = $this->apiService->getToken();
   $data['TokenId'] = $token; 

   $response = Http::timeout(90)->post("https://SightseeingBE.tektravels.com/SightseeingService.svc/rest/GetAvailability",[
    ""
   ]);


    }


    
}
