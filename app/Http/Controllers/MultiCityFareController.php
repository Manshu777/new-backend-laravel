<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Services\ApiService;

class MultiCityFareController extends Controller
{

    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }


    public function getMultiCityFare(Request $request)
    {
        $token = $this->apiService->getToken();
        try {
            $validated = $request->validate([
                'JourneyType' => 'integer',
                'EndUserIp' => 'ip',
                'Segments' => 'required|array',
                'Segments.*.Origin' => 'required|string',
                'Segments.*.Destination' => 'required|string',
                'Segments.*.FlightCabinClass' => 'required|integer',
                'Segments.*.PreferredDepartureTime' => 'required|date',
                'Days' => 'required|integer|min:1|max:30', // Number of days for price fetching
            ]);

            $apiUrl = "http://api.tektravels.com/BookingEngineService_Air/AirService.svc/rest/GetCalendarFare";

            // Prepare request data for multiple cities and dates
            $segments = [];
            foreach ($validated['Segments'] as $segment) {
                $preferredDates = $this->generateDates($segment['PreferredDepartureTime'], $validated['Days']);
                foreach ($preferredDates as $date) {
                    $segments[] = array_merge($segment, ['PreferredDepartureTime' => $date]);
                }
            }

            $response = Http::post($apiUrl, [
                "JourneyType" => $validated['JourneyType'],
                "EndUserIp" => $validated['EndUserIp'],
                "TokenId" => $token,
                "PreferredAirlines" => $request->input('PreferredAirlines', null),
                "Segments" => $segments,
                "Sources" => $request->input('Sources', null),
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            Log::error('API returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'Unable to fetch data from external API',
                'details' => $response->json(),
            ], $response->status());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('An error occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateDates($startDate, $days)
    {
        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = date('Y-m-d\TH:i:s', strtotime("+$i days", strtotime($startDate)));
        }
        return $dates;
    }
}
