<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use App\Services\ApiService;



class BusControllerSearch extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }


    public function searchBuses(Request $request)
    {
    
        $token = $this->apiService->getToken();

   
        $validatedData = $request->validate([
            'DateOfJourney' => 'required|date',
            'OriginId' => 'required|integer',
            'DestinationId' => 'required|integer',
            'PreferredCurrency' => 'required|string',
            'EndUserIp' => 'required|ip',  
        ]);

   
        $searchPayload = [
            "DateOfJourney" => $validatedData['DateOfJourney'],
            "DestinationId" => $validatedData['DestinationId'],
            "EndUserIp" => $validatedData['EndUserIp'], 
            "OriginId" => $validatedData['OriginId'],
            "TokenId" => $token,  
            "PreferredCurrency" => $validatedData['PreferredCurrency'],
        ];


        $response = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Search', $searchPayload);


        if ($response->json('Response.Error.ErrorCode') === 6) {

            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;


            $response = Http::timeout(90)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Search', $searchPayload);
        }


        return $response->json();
    }

    public function busBlock(Request $request)
    {
        $token = $this->apiService->getToken();
    
        $validatedData = $request->validate([
            'EndUserIp' => 'required|ip',
            'ResultIndex' => 'required|integer',
            'TraceId' => 'required|string',
            'BoardingPointId' => 'required|integer',
            'DroppingPointId' => 'required|integer',
            'Passenger' => 'required|array',
        ]);
    
        $payload = [
            "EndUserIp" => $validatedData['EndUserIp'],
            "ResultIndex" => $validatedData['ResultIndex'],
            "TraceId" => $validatedData['TraceId'],
            "TokenId" => $token,
            "BoardingPointId" => $validatedData['BoardingPointId'],
            "DroppingPointId" => $validatedData['DroppingPointId'],
            "Passenger" => $validatedData['Passenger'],
        ];
    
        $response = Http::timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://BusBE.tektravels.com/Busservice.svc/rest/Block', $payload);
    
        if ($response->json('BlockResult.Error.ErrorCode') === 6) {
            // Re-authenticate
            $token = $this->apiService->authenticate();
            $payload['TokenId'] = $token;
    
            // Retry request
            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://BusBE.tektravels.com/Busservice.svc/rest/Block', $payload);
        }
    
        return $response->json();
    }
    

    public function busSeatLayout(Request $request){
        $token = $this->apiService->getToken();
        
      
        $validatedData = $request->validate([
            'TraceId' => 'required',
            'ResultIndex' => 'required',
          
            'EndUserIp' => 'required|ip',  
        ]);

        $searchPayload = [
            "TraceId" => $validatedData['TraceId'],
            "ResultIndex" => $validatedData['ResultIndex'],
            "EndUserIp" => $validatedData['EndUserIp'],  // Use validated IP
            "TokenId" => $token,  // Use the token from the service
        ];

        $buslayout = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBusSeatLayOut', $searchPayload);
        $busBOARDING = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBoardingPointDetails', $searchPayload);

        
        if ($buslayout->json('Response.Error.ErrorCode') === 6) {
            // Re-authenticate to get a new token
            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;

            // Retry the API request with the new token
            $buslayout = Http::timeout(90)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBusSeatLayOut', $searchPayload);
            $busBOARDING = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBoardingPointDetails', $searchPayload);

        }
        // return $searchPayload;
        return   response()->json(["buslayout"=>json_decode($buslayout),"busbording"=>json_decode($busBOARDING)]);


    }
    public function bookbus(Request $request)
    {
        try {
            $token = $this->apiService->getToken();
    
            $validatedData = $request->validate([
                'TraceId' => 'required|string',
                'BoardingPointId' => 'required|integer',
                'DropingPointId' => 'required|integer',
                'ResultIndex' => 'required|string',
                'passenger' => 'required|array'
            ]);
    
            $searchData = [
                "EndUserIp" => "192.168.5.37",
                "ResultIndex" => $validatedData["ResultIndex"],
                "TraceId" => $validatedData["TraceId"],
                "TokenId" => $token,
                "BoardingPointId" => $validatedData["BoardingPointId"],
                "DropingPointId" => $validatedData["DropingPointId"],
                "Passenger" => $validatedData["passenger"]
            ];
    
            $bookbus = Http::timeout(90)->post('https://BusBE.tektravels.com/Busservice.svc/rest/Book', $searchData);
    
            // Log full API response for debugging
            \Log::info('Book Bus API Response:', $bookbus->json());
    
            // Try to retrieve error info safely
            $errorCode = data_get($bookbus->json(), 'Response.Error.ErrorCode');
            $errorMessage = data_get($bookbus->json(), 'Response.Error.ErrorMessage');
    
            // Handle token expiration (ErrorCode 6)
            if ($errorCode === 6) {
                $token = $this->apiService->authenticate();
                $searchData['TokenId'] = $token;
                $bookbus = Http::timeout(90)->post('https://BusBE.tektravels.com/Busservice.svc/rest/Book', $searchData);
    
                // Update error info again after re-auth
                $errorCode = data_get($bookbus->json(), 'Response.Error.ErrorCode');
                $errorMessage = data_get($bookbus->json(), 'Response.Error.ErrorMessage');
            }
    
            // If there's any error code other than 0, return it
            if ($errorCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage ?? 'Booking failed',
                    'error_code' => $errorCode,
                    'response' => $bookbus->json()
                ], 400);
            }
    
            return response()->json([
                'success' => true,
                'data' => $bookbus->json()
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => 'HTTP request failed',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

   
}