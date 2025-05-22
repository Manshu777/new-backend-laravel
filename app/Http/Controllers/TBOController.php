<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\HotelCity;
use App\Models\Tbohotelcodelist;
use Carbon\Carbon;

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
        // Check for valid data in the database (within 15 days)
        $validCities = HotelCity::where('country_code', $request->CountryCode)
            ->where('created_at', '>=', Carbon::now()->subDays(15))
            ->get();

        // If valid data exists, use it
        if ($validCities->isNotEmpty()) {
            $cities = $validCities->map(function ($city) {
                return [
                    'Code' => $city->code,
                    'Name' => $city->name,
                ];
            })->toArray();

            // Apply search filter if provided
            $search = $request->query('search');
            if ($search) {
                $search = strtolower($search);
                $filteredCities = array_filter($cities, function ($city) use ($search) {
                    return strpos(strtolower($city['Name']), $search) !== false;
                });
                return response()->json(array_values($filteredCities), 200);
            }

            // Return the first 20 cities if no search query
            return response()->json(array_slice($cities, 0, 20), 200);
        }

        // No valid data or data expired, fetch from API
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}"),
            'Content-Type' => 'application/json',
        ])->post($this->cityApiUrl, [
            'CountryCode' => $request->CountryCode,
        ]);

        // Check if the API request was successful
        if ($response->successful()) {
            $apiCities = $response->json()['CityList'] ?? null;

            // Validate API response
            if (is_null($apiCities) || !is_array($apiCities)) {
                return response()->json([
                    'error' => 'Invalid response from the API',
                    'message' => 'CityList not found in the API response',
                ], 500);
            }

            // Get existing city codes from the database for the given country code
            $existingCityCodes = HotelCity::where('country_code', $request->CountryCode)
                ->pluck('code')
                ->toArray();

            // Process and save new cities
            foreach ($apiCities as $apiCity) {
                if (!in_array($apiCity['Code'], $existingCityCodes)) {
                    // Add new city if code doesn't exist
                    HotelCity::create([
                        'code' => $apiCity['Code'],
                        'name' => $apiCity['Name'],
                        'country_code' => $request->CountryCode,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
        

                    // Update existing city codes array
                    $existingCityCodes[] = $apiCity['Code'];
                }
            }

            // Retrieve all cities from the database after updating
            $cities = HotelCity::where('country_code', $request->CountryCode)
                ->where('created_at', '>=', Carbon::now()->subDays(15))
                ->get()
                ->map(function ($city) {
                    return [
                        'Code' => $city->code,
                        'Name' => $city->name,
                    ];
                })->toArray();

            // Apply search filter if provided
            $search = $request->query('search');
            if ($search) {
                $search = strtolower($search);
                $filteredCities = array_filter($cities, function ($city) use ($search) {
                    return strpos(strtolower($city['Name']), $search) !== false;
                });
                return response()->json(array_values($filteredCities), 200);
            }

            // Return the first 20 cities if no search query
            return response()->json(array_slice($cities, 0, 20), 200);
        } else {
            // Handle unsuccessful responses
            return response()->json([
                'error' => 'Failed to fetch cities',
                'message' => $response->json() ?? 'No response body found',
            ], $response->status());
        }
    } catch (\Illuminate\Http\Client\RequestException $e) {
        return response()->json([
            'error' => 'Network or request error',
            'message' => $e->getMessage(),
        ], 502);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'An unexpected error occurred',
            'message' => $e->getMessage(),
        ], 500);
    }
}


    // Method to fetch hotels
    public function fetchHotels(Request $request)
    {

        $request->validate([
        'CityCode' => 'required|string',
    ]);

    // Make API request
    $response = Http::withHeaders([
        'Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}"),
        'Content-Type' => 'application/json',
    ])->post($this->hotelApiUrl, [
        'CityCode' => $request->CityCode,
        'IsDetailedResponse' => true,
    ]);

    // Check if the response is successful
    if ($response->successful()) {
        $hotels = $response->json();

        // Assuming the JSON response is an array of hotels
        foreach ($hotels as $hotelData) {
            Tbohotelcodelist::updateOrCreate(
                ['hotel_code' => $hotelData['HotelCode']], // Unique key
                [
                    'hotel_name' => $hotelData['HotelName'],
                    'latitude' => $hotelData['Latitude'],
                    'longitude' => $hotelData['Longitude'],
                    'hotel_rating' => $hotelData['HotelRating'],
                    'address' => $hotelData['Address'],
                    'country_name' => $hotelData['CountryName'],
                    'country_code' => $hotelData['CountryCode'],
                    'city_name' => $hotelData['CityName'],
                    'expires_at' => Carbon::now()->addDays(15), // Set expiration to 15 days
                ]
            );
        }
    }

    return response()->json($response->json(), $response->status());
    
    }
}
