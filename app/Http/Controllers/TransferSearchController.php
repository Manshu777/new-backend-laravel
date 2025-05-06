<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Facades\Http;


class TransferSearchController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function searchTransfer(Request $request)
    {
        // Validate the request parameters
        $validated = $request->validate([
            'transferTime' => 'required|string', // e.g., "1030"
            'transferDate' => 'required|date',   // e.g., "2021-01-07"
            'adultCount' => 'required|integer|min:1',
            'preferredLanguage' => 'required|integer',
            'alternateLanguage' => 'required|integer',
            'preferredCurrency' => 'required|string',
            'pickupCode' => 'required|integer',
            'pickupPointCode' => 'required|string', // e.g., "LGW"
            'cityId' => 'required|string', // e.g., "126632"
            'dropoffCode' => 'required|integer',
            'dropoffPointCode' => 'required|string', // e.g., "LHR"
            'countryCode' => 'required|string', // e.g., "GB"
        ]);
  
        try {
            // Retrieve the token using the ApiService
            $token = $this->apiService->getToken();

            // Prepare the payload for the API request
            $requestData = [
                "TransferTime" => $validated['transferTime'],
                "TransferDate" => $validated['transferDate'],
                "AdultCount" => $validated['adultCount'],
                "PreferredLanguage" => $validated['preferredLanguage'],
                "AlternateLanguage" => $validated['alternateLanguage'],
                "PreferredCurrency" => $validated['preferredCurrency'],
                "IsBaseCurrencyRequired" => false,
                "PickUpCode" => $validated['pickupCode'],
                "PickUpPointCode" => $validated['pickupPointCode'],
                "CityId" => $validated['cityId'],
                "DropOffCode" => $validated['dropoffCode'],
                "DropOffPointCode" => $validated['dropoffPointCode'],
                "CountryCode" => $validated['countryCode'],
                "EndUserIp" => $request->ip(), // Automatically capture the client's IP
                "TokenId" => $token,
            ];

            // Make the API request
            $response = Http::post(
                'http://api.tektravels.com/BookingEngineService_Transfer/TransferService.svc/rest/Search/',
                $requestData
            );

            // Check if the response was successful
            if ($response->successful()) {
                $data = $response->json();
                return response()->json($data);
            } else {
                // Handle API response errors
                return response()->json(['error' => 'Failed to fetch transfer search results.'], $response->status());
            }
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
