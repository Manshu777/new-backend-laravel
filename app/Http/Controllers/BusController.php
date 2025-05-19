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
        "IpAddress" => '148.135.137.54',
        "ClientId" => 'ApiIntegrationNew',
    ];

    $response = Http::timeout(100)
        ->post('https://Sharedapi.tektravels.com/StaticData.svc/rest/GetBusCityList', $searchPayload);

    if ($response->json('Response.Error.ErrorCode') === 6) {
        $token = $this->apiService->authenticate();
        $searchPayload['TokenId'] = $token;
        $response = Http::timeout(90)
            ->post('https://Sharedapi.tektravels.com/StaticData.svc/rest/GetBusCityList', $searchPayload);
    }

    $busCities = $response->json('BusCities') ?? [];

    // Apply search filter by CityName
    $search = $request->input('search', '');
    if ($search) {
        $busCities = array_filter($busCities, function ($city) use ($search) {
            return stripos($city['CityName'], $search) !== false;
        });
        // When searching, return all matching results without pagination
        $paginatedData = array_values($busCities); // Re-index array
        $limit = count($paginatedData); // Set limit to total results
        $offset = 0; // No offset for search results
    } else {
        // Apply pagination when no search term is provided
        $limit = $request->input('limit', 100); // default 100
        $offset = $request->input('offset', 0); // default 0
        $paginatedData = array_slice($busCities, $offset, $limit);
    }

    return response()->json([
        'total' => count($busCities),
        'limit' => $limit,
        'offset' => $offset,
        'BusCities' => $paginatedData,
    ]);
}

}
