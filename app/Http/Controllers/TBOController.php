<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TBOController extends Controller
{
    // TBO API Credentials
    private $username = 'TBOStaticAPITest';
    private $password = 'Tbo@11530818';

    // Base URLs for the TBO APIs
    private $cityApiUrl = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList";
    private $hotelApiUrl = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/TBOHotelCodeList";
    private $hoteldetalapi = "https://affiliate.tektravels.com/HotelAPI/Search";
    // Method to fetch cities
    public function fetchCities(Request $request)
    {
        $request->validate([
            'CountryCode' => 'required|string|max:2',
            'search' => 'nullable|string',
        ]);

        try {
            // API Request
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}"),
                'Content-Type' => 'application/json',
            ])->post($this->cityApiUrl, [
                'CountryCode' => $request->CountryCode,
            ]);

            // Check if the API request was successful
            if ($response->successful()) {
                $cities = $response->json()['CityList'] ?? null;

                // Check if 'CityList' is available in the response
                if (is_null($cities) || !is_array($cities)) {
                    return response()->json([
                        'error' => 'Invalid response from the API',
                        'message' => 'CityList not found in the API response',
                    ], 500); // Internal Server Error
                }

                // Retrieve the 'search' parameter from the URL query string
                $search = $request->query('search');

                // If search parameter is provided, filter the city list
                if ($search) {
                    $search = strtolower($search);
                    $filteredCities = array_filter($cities, function ($city) use ($search) {
                        return strpos(strtolower($city['Name']), $search) !== false;
                    });
                    return response()->json(array_values($filteredCities), 200);
                }

                // If no search query is provided, return the first 20 cities
                $defaultCities = array_slice($cities, 0, 20);
                return response()->json($defaultCities, 200);
            } else {
                // Handle unsuccessful responses
                return response()->json([
                    'error' => 'Failed to fetch cities',
                    'message' => $response->json() ?? 'No response body found',
                ], $response->status());
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Catch request-related exceptions (e.g., connection issues, timeouts)
            return response()->json([
                'error' => 'Network or request error',
                'message' => $e->getMessage(),
            ], 502); // Bad Gateway
        } catch (\Exception $e) {
            // General exception catch block
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }


    // Method to fetch hotels
    public function fetchHotels(Request $request)
    {
        // Validate request
        $request->validate([
            'CityCode' => 'required|string',
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}"),
            'Content-Type' => 'application/json',
        ])->post($this->hotelApiUrl, [
            'CityCode' => $request->CityCode,
            'IsDetailedResponse' => true,
        ]);



        return response()->json($response->json(), $response->status());
    }
}
