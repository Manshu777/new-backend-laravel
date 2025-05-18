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

 


    public function searchBusCityList(Request $request)
    {
        $token = $this->apiService->getToken();

        

        $searchPayload = [
            "TokenId" => $token,
            "IpAddress" =>  '148.135.137.54',
            "ClientId" => 'ApiIntegrationNew',
        ];

        $response = Http::timeout(100)
            ->withHeaders([])
            ->post('https://api.travelboutiqueonline.com/SharedAPI/StaticData.svc/rest/GetBusCityList', $searchPayload);

        if ($response->json('Response.Error.ErrorCode') === 6) {
            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;
             $response = Http::timeout(90)
                ->withHeaders([])
                ->post('https://api.travelboutiqueonline.com/SharedAPI/StaticData.svc/rest/GetBusCityList', $searchPayload);
        }

       
        $busCities = $response->json('BusCities');

        

    
        return response()->json([
            'BusCities' => $busCities
        ]);
    }
}
