<?php

namespace App\Http\Controllers;

use App\Services\ApiService; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class TransferController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function getTransferData(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'cityId' => 'required|string',
            'transferCategoryType' => 'required|in:1,2,3,4',
        ]);

        // Retrieve the API token using the ApiService
        $token = $this->apiService->getToken();

        // Prepare the payload for the API request
        $requestData = [
            "CityId" => $validated['cityId'],
            "ClientId" => "apiintegrationnew",
            "EndUserIp" => $request->ip(), // Automatically get the user's IP
            "TransferCategoryType" => $validated['transferCategoryType'],
            "TokenId" => $token,
        ];

        try {
            // Make the API request using the HTTP client
            $response = Http::post(
                'http://sharedapi.tektravels.com/staticdata.svc/rest/GetTransferStaticData',
                $requestData
            );

            // Check if the response is successful
            if ($response->successful()) {
                $transferData = json_decode($response->body(), true);
                return response()->json($transferData);
            } else {
                // Handle API errors
                return response()->json(['error' => 'Unable to fetch transfer data.'], $response->status());
            }
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
