<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Facades\Http;


use Illuminate\Support\Facades\Validator;
class TransferSearchController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

     
     public function searchTransfer(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'TransferTime' => 'required|string|regex:/^[0-9]{4}$/',
            'TransferDate' => 'required|date|date_format:Y-m-d',
            'AdultCount' => 'required|integer|min:1',
            'PreferredLanguage' => 'required|integer',
            'AlternateLanguage' => 'required|integer',
            'PreferredCurrency' => 'required|string|size:3',
            'PickUpCode' => 'required|integer',
            'PickUpPointCode' => 'required|string',
            'CityId' => 'required',
            'DropOffCode' => 'required|integer',
            'DropOffPointCode' => 'required|string',
            'CountryCode' => 'required|string|size:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        // Get validated data
        $validated = $validator->validated();

        // Prepare payload
        $searchPayload = [
            'TransferTime' => $validated['TransferTime'],
            'TransferDate' => $validated['TransferDate'],
            'AdultCount' => $validated['AdultCount'],
            'PreferredLanguage' => $validated['PreferredLanguage'],
            'AlternateLanguage' => $validated['AlternateLanguage'],
            'PreferredCurrency' => $validated['PreferredCurrency'],
            'IsBaseCurrencyRequired' => false,
            'PickUpCode' => $validated['PickUpCode'],
            'PickUpPointCode' => $validated['PickUpPointCode'],
            'CityId' => $validated['CityId'],
            'DropOffCode' => $validated['DropOffCode'],
            'DropOffPointCode' => $validated['DropOffPointCode'],
            'CountryCode' => $validated['CountryCode'],
            'EndUserIp' => $request->ip(),
            'TokenId' => $this->apiService->getToken(),
        ];

        try {
            $response = Http::timeout(100)->post(
                'https://TransferBE.tektravels.com/TransferService.svc/rest/Search',
                $searchPayload
            );

            $results = $response->json();

            // Handle token expiration
            if (isset($results['Response']['Error']['ErrorCode']) && $results['Response']['Error']['ErrorCode'] === 6) {
                $searchPayload['TokenId'] = $this->apiService->authenticate();
                
                $response = Http::timeout(100)->post(
                    'https://TransferBE.tektravels.com/TransferService.svc/rest/Search',
                    $searchPayload
                );
                
                $results = $response->json();
            }

            // Check for API errors
            if (isset($results['Response']['Error']['ErrorCode']) && $results['Response']['Error']['ErrorCode'] !== 0) {
                return response()->json([
                    'error' => 'API error',
                    'message' => $results['Response']['Error']['ErrorMessage']
                ], 400);
            }

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

      


}
