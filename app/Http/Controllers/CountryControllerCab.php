<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\ApiService; // Adjust the namespace based on your project structure
use Illuminate\Support\Facades\Http;

class CountryControllerCab extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    public function getCountryList(Request $request)
    {
    
        $token = $this->apiService->getToken();

    
        $requestData = [
            "TokenId" => $token,
            "ClientId" => "tboprod",
            "EndUserIp" => $request->ip() 
        ];

        try {
     
            $response = Http::post('http://api.tektravels.com/SharedServices/SharedData.svc/rest/CountryList', $requestData);

            // Check for a successful response
            if ($response->successful()) {
                // Decode the JSON response
                $countryList = json_decode($response->body(), true);
                
                return response()->json($countryList);
            } else {
                // Handle error response
                return response()->json(['error' => 'Unable to fetch country list.'], $response->status());
            }
        } catch (\Exception $e) {
        
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
