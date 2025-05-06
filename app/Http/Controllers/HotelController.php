<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use Exception;
use Illuminate\Http\JsonResponse;

class HotelController extends Controller
{
    public function getHotelDetails()
    {
        // Step 1: Initialize Guzzle Client
        $client = new \GuzzleHttp\Client();
    

        $authUsername = 'TBOStaticAPITest';
        $authPassword = 'Tbo@11530818';
    
        try {
       
            $hotelCodeListUrl = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/TBOHotelCodeList';
            $response = $client->post($hotelCodeListUrl, [
                'auth' => [$authUsername, $authPassword], 
                'json' => [
                    'CityCode' => '100881', 
                    'IsDetailedResponse' => 'false',
                ],
                'query' => [
                    'Language' => 'EN', // Language preference
                ]
            ]);
    
            // Decode the response to get hotel codes
            $responseData = json_decode($response->getBody()->getContents(), true);
    
            if (!isset($responseData['Hotels'])) {
                return response()->json(['error' => 'No hotel data found'], 404);
            }
    
            // Retrieve the first 5 hotels and get only the hotel codes
            $hotels = array_slice($responseData['Hotels'], 0, 5);
            $hotelCodes = [];
    
            foreach ($hotels as $hotel) {
                $hotelCodes[] = $hotel['HotelCode']; // Extract hotel codes
            }
    
            // Return or print just the list of hotel codes
            return response()->json($hotelCodes);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error fetching hotel codes: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }
    
    
    

}
