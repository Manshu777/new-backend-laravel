<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ApiService;

class BusController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    // https://Sharedapi.tektravels.com/StaticData.svc/rest/GetBusCityList
    // Method to search bus cities


    public function searchBusCityList(Request $request)
    {
        $token = $this->apiService->getToken();

        

        $searchPayload = [
            "TokenId" => $token,
            "IpAddress" =>  '223.178.208.102', // Use provided IP or fallback to a default
            "ClientId" => 'ApiIntegrationNew',
        ];

        $response = Http::timeout(100)
            ->withHeaders([])
            ->post('https://Sharedapi.tektravels.com/StaticData.svc/rest/GetBusCityList', $searchPayload);

        if ($response->json('Response.Error.ErrorCode') === 6) {
            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;
             $response = Http::timeout(90)
                ->withHeaders([])
                ->post('https://Sharedapi.tektravels.com/StaticData.svc/rest/GetBusCityList', $searchPayload);
        }

       
        $busCities = $response->json('BusCities');

        

    
        return response()->json([
            'BusCities' => $busCities
        ]);
    }
}
