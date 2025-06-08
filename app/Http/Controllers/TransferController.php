<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Destination;
use Illuminate\Pagination\LengthAwarePaginator;

class TransferController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * GET route to retrieve paginated transfer data from the API
     */
    public function getTransfers(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'cityId' => 'required|string',
            'transferCategoryType' => 'required|in:1,2,3,4',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        // Retrieve the API token using the ApiService
        try {
            $token = $this->apiService->getToken();
            if (!$token) {
                Log::error('Failed to retrieve API token');
                return response()->json(['error' => 'Unable to retrieve API token'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception while retrieving API token: ' . $e->getMessage());
            return response()->json(['error' => 'Exception while retrieving API token', 'message' => $e->getMessage()], 500);
        }

        // Prepare query parameters for the API request
        $queryParams = [
            'CityId' => $validated['cityId'],
            'ClientId' => config('services.tektravels.client_id_transfer', 'tboprod'),
            'EndUserIp' => $request->ip(),
            'TransferCategoryType' => $validated['transferCategoryType'],
            'TokenId' => $token,
        ];

        try {
            // Make the API request with timeout
            $response = Http::timeout(15)->get(
                config('services.tektravels.transfer_api_url', 'http://sharedapi.tektravels.com/staticdata.svc/rest/GetTransferStaticData'),
                $queryParams
            );

            if ($response->successful()) {
                $transferData = json_decode($response->body(), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Invalid JSON format received: ' . json_last_error_msg());
                    return response()->json(['error' => 'Malformed JSON response from API'], 500);
                }

                if (!isset($transferData['Transfers'])) {
                    Log::error('Invalid API response: Missing Transfers key', ['response' => $transferData]);
                    return response()->json(['error' => 'Invalid API response format'], 500);
                }

                $transfers = $transferData['Transfers'] ?? [];
                $limit = $validated['limit'] ?? 100;
                $page = $validated['page'] ?? 1;

                // Paginate the transfers
                $perPage = $limit;
                $currentPage = $page;
                $offset = ($currentPage - 1) * $perPage;
                $paginatedTransfers = array_slice($transfers, $offset, $perPage);
                $total = count($transfers);

                $paginator = new LengthAwarePaginator(
                    $paginatedTransfers,
                    $total,
                    $perPage,
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                return response()->json([
                    'message' => !empty($transfers) ? 'Transfers retrieved successfully' : 'No transfers found for the given parameters.',
                    'count' => $total,
                    'data' => $paginator->items(),
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                    ],
                ], 200);
            } else {
                Log::error('API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Unable to fetch transfer data.'], $response->status());
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP client error: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP client error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET route to retrieve paginated destination data from the database
     */
    public function getDestinations(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $limit = $validated['limit'] ?? 100;

        // Query the Destination model with pagination
        $destinations = Destination::query()
            ->select('destination_id', 'city_name', 'country_code', 'country_name', 'state_province', 'type')
            ->paginate($limit);

        return response()->json([
            'message' => $destinations->isNotEmpty() ? 'Destinations retrieved successfully' : 'No destinations found.',
            'count' => $destinations->total(),
            'data' => $destinations->items(),
            'pagination' => [
                'current_page' => $destinations->currentPage(),
                'last_page' => $destinations->lastPage(),
                'per_page' => $destinations->perPage(),
                'total' => $destinations->total(),
            ],
        ], 200);
    }

    /**
     * Original POST method for fetching and saving destination data
     */
    public function getDestinationSearchStaticData(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'SearchType' => 'required|in:1,2',
            'CountryCode' => 'required|string|size:2',
            'Limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        // Retrieve the API token using the ApiService
        try {
            $token = $this->apiService->getToken();
            if (!$token) {
                Log::error('Failed to retrieve API token');
                return response()->json(['error' => 'Unable to retrieve API token'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception while retrieving API token: ' . $e->getMessage());
            return response()->json(['error' => 'Exception while retrieving API token', 'message' => $e->getMessage()], 500);
        }

        // Prepare the payload for the API request
        $requestData = [
            'ClientId' => config('services.tektravels.client_id_destination', 'tboprod'),
            'EndUserIp' => $request->ip(),
            'TokenId' => $token,
            'SearchType' => $validated['SearchType'],
            'CountryCode' => $validated['CountryCode'],
            'Limit' => $validated['Limit'] ?? 100,
        ];

        try {
            // Make the API request with timeout
            $response = Http::timeout(15)->post(
                config('services.tektravels.destination_api_url', 'http://sharedapi.tektravels.com/staticdata.svc/rest/GetDestinationSearchStaticData'),
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
            $limit = $validated['Limit'] ?? 100;
            $page = $validated['page'] ?? 1;

            // Save all destinations to the database
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
                    Log::error('Failed to store destination data: ' . $e->getMessage());
                    return response()->json(['error' => 'Failed to store destination data', 'message' => $e->getMessage()], 500);
                }
            }

            // Paginate the destinations for the response
            $perPage = $limit;
            $currentPage = $page;
            $offset = ($currentPage - 1) * $perPage;
            $paginatedDestinations = array_slice($destinations, $offset, $perPage);
            $total = count($destinations);

            $paginator = new LengthAwarePaginator(
                $paginatedDestinations,
                $total,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return response()->json([
                'message' => !empty($destinations) ? 'Destinations retrieved and saved successfully' : 'No destinations found for the given parameters.',
                'count' => $total,
                'data' => $paginator->items(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ], 200);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP client error: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP client error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error: ' . $e->getMessage());
            return response()->json(['error' => 'Unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Original POST method for fetching transfer data
     */
    public function getTransferData(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'cityId' => 'required|string',
            'transferCategoryType' => 'required|in:1,2,3,4',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        // Retrieve the API token using the ApiService
        try {
            $token = $this->apiService->getToken();
            if (!$token) {
                Log::error('Failed to retrieve API token');
                return response()->json(['error' => 'Unable to retrieve API token'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception while retrieving API token: ' . $e->getMessage());
            return response()->json(['error' => 'Exception while retrieving API token', 'message' => $e->getMessage()], 500);
        }

        // Prepare the payload for the API request
        $requestData = [
            'CityId' => $validated['cityId'],
            'ClientId' => config('services.tektravels.client_id_transfer', 'tboprod'),
            'EndUserIp' => $request->ip(),
            'TransferCategoryType' => $validated['transferCategoryType'],
            'TokenId' => $token,
        ];

        try {
            // Make the API request with timeout
            $response = Http::timeout(15)->post(
                config('services.tektravels.transfer_api_url', 'http://sharedapi.tektravels.com/staticdata.svc/rest/GetTransferStaticData'),
                $requestData
            );

            if ($response->successful()) {
                $transferData = json_decode($response->body(), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Invalid JSON format received: ' . json_last_error_msg());
                    return response()->json(['error' => 'Malformed JSON response from API'], 500);
                }

                if (!isset($transferData['Transfers'])) {
                    Log::error('Invalid API response: Missing Transfers key', ['response' => $transferData]);
                    return response()->json(['error' => 'Invalid API response format'], 500);
                }

                $transfers = $transferData['Transfers'] ?? [];
                $limit = $validated['limit'] ?? 100;
                $page = $validated['page'] ?? 1;

                // Paginate the transfers
                $perPage = $limit;
                $currentPage = $page;
                $offset = ($currentPage - 1) * $perPage;
                $paginatedTransfers = array_slice($transfers, $offset, $perPage);
                $total = count($transfers);

                $paginator = new LengthAwarePaginator(
                    $paginatedTransfers,
                    $total,
                    $perPage,
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                return response()->json([
                    'message' => !empty($transfers) ? 'Transfers retrieved successfully' : 'No transfers found for the given parameters.',
                    'count' => $total,
                    'data' => $paginator->items(),
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                    ],
                ], 200);
            } else {
                Log::error('API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Unable to fetch transfer data.'], $response->status());
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP client error: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP client error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}