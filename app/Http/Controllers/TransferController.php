<?php

namespace App\Http\Controllers;

use App\Services\ApiService; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Destination;
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

  public function getDestinationSearchStaticData(Request $request)
{
    // Validate incoming request
    $validated = $request->validate([
        'SearchType' => 'required|in:1,2', // 1 = City, 2 = Hotel
        'CountryCode' => 'required|string|size:2', // ISO Country Code (e.g., GB)
        'Limit' => 'nullable|integer|min:1|max:100', // Optional limit, default 100
    ]);

    // Retrieve the API token using the ApiService
    try {
        $token = $this->apiService->getToken();
        if (!$token) {
            Log::error('Failed to retrieve API token');
            return response()->json(['error' => 'Unable to retrieve API token'], 500);
        }
    } catch (\Exception $e) {
        report($e);
        return response()->json(['error' => 'Exception while retrieving API token', 'message' => $e->getMessage()], 500);
    }

    // Prepare the payload for the API request
    $requestData = [
        'ClientId' => 'ApiIntegrationNew',
        'EndUserIp' => $request->ip(),
        'TokenId' => $token,
        'SearchType' => $validated['SearchType'],
        'CountryCode' => $validated['CountryCode'],
        'Limit' => $validated['Limit'] ?? 100,
    ];

    try {
        // Make the API request with timeout and exception handling
        $response = Http::timeout(15)->post(
            'http://sharedapi.tektravels.com/staticdata.svc/rest/GetDestinationSearchStaticData',
            $requestData
        );

        if (!$response->successful()) {
            Log::error('API call failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return response()->json(['error' => 'Unable to fetch destination data from external API'], $response->status());
        }

        $destinationData = json_decode($response->body(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON format received: ' . json_last_error_msg());
            return response()->json(['error' => 'Malformed JSON response from API'], 500);
        }

        $destinations = $destinationData['Destinations'] ?? [];

        if (!empty($destinations)) {
            $destinationRecords = array_map(function ($destination) {
                return [
                    'city_name' => $destination['CityName'] ?? '',
                    'country_code' => $destination['CountryCode'] ?? '',
                    'country_name' => $destination['CountryName'] ?? '',
                    'destination_id' => $destination['DestinationId'] ?? 0,
                    'state_province' => $destination['StateProvince'] ?? null,
                    'type' => $destination['Type'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $destinations);

            try {
                DB::transaction(function () use ($destinationRecords) {
                    foreach (array_chunk($destinationRecords, 100) as $chunk) {
                        Destination::upsert(
                            $chunk,
                            ['destination_id'],
                            ['city_name', 'country_code', 'country_name', 'state_province', 'type', 'updated_at']
                        );
                    }
                });
            } catch (\Exception $e) {
                report($e);
                return response()->json(['error' => 'Failed to store destination data', 'message' => $e->getMessage()], 500);
            }

            return response()->json([
                'message' => 'Destinations saved successfully',
                'count' => count($destinations),
                'data' => $destinations,
            ]);
        }

        return response()->json([
            'message' => 'No destinations found for the given parameters.',
            'data' => [],
        ], 200);

    } catch (\Illuminate\Http\Client\RequestException $e) {
        report($e);
        return response()->json(['error' => 'HTTP client error', 'message' => $e->getMessage()], 500);
    } catch (\Exception $e) {
        report($e);
        return response()->json(['error' => 'Unexpected error occurred', 'message' => $e->getMessage()], 500);
    }
}

}
