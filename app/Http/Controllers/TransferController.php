<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Destination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\DestinationCity;
use App\Models\DestinationHotel;
use Illuminate\Support\Facades\Cache;


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
                config('services.tektravels.transfer_api_url', 'https://api.travelboutiqueonline.com/TransferAPI_V10/TransferService.svc/GetTransferStaticData'),
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

    
     public function getDestinationSearchStaticData(Request $request)
     {
         // Step 1: Validation
         $validated = $request->validate([
             'SearchType' => 'required|in:1,2',
             'CountryCode' => 'required|string|size:2',
             'Limit' => 'nullable|integer|min:1|max:1000',
             'page' => 'nullable|integer|min:1',
         ]);
 
         $limit = $validated['Limit'] ?? 100;
         $page = $validated['page'] ?? 1;
         $type = (int)$validated['SearchType'];
         $cacheKey = "destinations_{$type}_{$validated['CountryCode']}_{$limit}_{$page}";
 
         // Step 2: Check cache
         $cachedData = Cache::get($cacheKey);
         if ($cachedData) {
             return response()->json($cachedData, 200);
         }
 
         // Step 3: Check database
         $records = $this->getDataFromDatabase($type, $validated['CountryCode']);
         if ($records) {
             return $this->paginateAndRespond($records, $request, $limit, $page);
         }
 
         // Step 4: Get API token
         try {
             $token = $this->apiService->getToken();
             if (!$token) {
                 Log::error('API token failed');
                 return response()->json(['error' => 'Unable to retrieve API token'], 500);
             }
         } catch (\Exception $e) {
             Log::error('Token exception: ' . $e->getMessage());
             return response()->json(['error' => 'Token retrieval exception', 'message' => $e->getMessage()], 500);
         }
 
         // Step 5: Prepare API request
         $requestData = [
             'ClientId' => config('services.tektravels.client_id_destination', 'tboprod'),
             'EndUserIp' => $request->ip(),
             'TokenId' => $token,
             'SearchType' => $validated['SearchType'],
             'CountryCode' => $validated['CountryCode'],
             'Limit' => $validated['Limit'] ?? 1000,
         ];
 
         try {
             // Step 6: Make API request
             $response = Http::timeout(30)->post(
                 config('services.tektravels.destination_api_url', 'http://sharedapi.tektravels.com/staticdata.svc/rest/GetDestinationSearchStaticData'),
                 $requestData
             );
 
             if (!$response->successful()) {
                 Log::error('API failed', ['status' => $response->status(), 'body' => $response->body()]);
                 return response()->json(['error' => 'API call failed'], $response->status());
             }
 
             $data = json_decode($response->body(), true);
             if (json_last_error() !== JSON_ERROR_NONE) {
                 Log::error('Malformed JSON: ' . json_last_error_msg());
                 return response()->json(['error' => 'Malformed JSON'], 500);
             }
 
             $destinations = $data['Destinations'] ?? [];
             $records = [];
 
             // Step 7: Process API data
             foreach ($destinations as $destination) {
                 if ($type === 1) { // City
                     $records[] = [
                         'destination_id' => $destination['DestinationId'] ?? 0,
                         'city_name' => $destination['CityName'] ?? '',
                         'state_province' => $destination['StateProvince'] ?? '',
                         'country_code' => $destination['CountryCode'] ?? '',
                         'country_name' => $destination['CountryName'] ?? '',
                         'type' => $type,
                         'created_at' => now(),
                         'updated_at' => now(),
                     ];
                 } elseif ($type === 2) { // Hotel
                     $records[] = [
                         'destination_id' => $destination['DestinationId'] ?? 0,
                         'hotel_name' => $destination['HotelName'] ?? '',
                         'city_name' => $destination['CityName'] ?? '',
                         'state_province' => $destination['StateProvince'] ?? '',
                         'country_code' => $destination['CountryCode'] ?? '',
                         'country_name' => $destination['CountryName'] ?? '',
                         'type' => $type,
                         'created_at' => now(),
                         'updated_at' => now(),
                     ];
                 }
             }
 
             // Step 8: Store in database
             if ($records) {
                 DB::transaction(function () use ($records, $type) {
                     foreach (array_chunk($records, 1000) as $chunk) {
                         if ($type === 1) {
                             DestinationCity::upsert($chunk, ['destination_id'], ['city_name', 'state_province', 'country_code', 'country_name', 'type', 'updated_at']);
                         } elseif ($type === 2) {
                             DestinationHotel::upsert($chunk, ['destination_id'], ['hotel_name', 'city_name', 'state_province', 'country_code', 'country_name', 'type', 'updated_at']);
                         }
                     }
                 });
             }
 
             // Step 9: Cache and return response
             $responseData = $this->paginateAndRespond($records, $request, $limit, $page);
             Cache::put($cacheKey, $responseData->getData(true), now()->addHour());
             return $responseData;
 
         } catch (\Illuminate\Http\Client\RequestException $e) {
             Log::error('HTTP client exception: ' . $e->getMessage());
             return response()->json(['error' => 'Client error', 'message' => $e->getMessage()], 500);
         } catch (\Exception $e) {
             Log::error('Unexpected exception: ' . $e->getMessage());
             return response()->json(['error' => 'Unexpected error', 'message' => $e->getMessage()], 500);
         }
     }
 
     /**
      * Retrieve data from database based on SearchType and CountryCode
      */
     private function getDataFromDatabase($type, $countryCode)
     {
         $query = $type === 1 ? DestinationCity::query() : DestinationHotel::query();
         $records = $query->where('country_code', $countryCode)
                          ->where('type', $type)
                          ->get()
                          ->map(function ($item) {
                              return $item->toArray();
                          })
                          ->toArray();
 
         return $records ?: null;
     }
 
     /**
      * Paginate records and format response
      */
     private function paginateAndRespond($records, Request $request, $limit, $page)
     {
         $offset = ($page - 1) * $limit;
         $paginated = array_slice($records, $offset, $limit);
         $total = count($records);
 
         $paginator = new LengthAwarePaginator($paginated, $total, $limit, $page, [
             'path' => $request->url(),
             'query' => $request->query(),
         ]);
 
         return response()->json([
             'message' => $total ? 'Destinations retrieved successfully' : 'No destinations found.',
             'count' => $total,
             'data' => $paginator->items(),
             'pagination' => [
                 'current_page' => $paginator->currentPage(),
                 'last_page' => $paginator->lastPage(),
                 'per_page' => $paginator->perPage(),
                 'total' => $paginator->total(),
             ],
         ], 200);
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