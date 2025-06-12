<?php

namespace App\Http\Controllers;

use App\Models\WeddingEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WeddingEventController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validate request
            $data = $request->validate([
                'hostName' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:15',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'address' => 'nullable|string',
                'brideName' => 'nullable|string|max:255',
                'groomName' => 'nullable|string|max:255',
                'weddingTitle' => 'nullable|string|max:255',
                'weddingDescription' => 'nullable|string',
                'startDate' => 'required|date_format:Y-m-d',
                'endDate' => 'nullable|date_format:Y-m-d|after:startDate',
                'venue' => 'nullable|string|max:255',
                'guests' => 'nullable|integer|min:0',
                'languages' => 'nullable|string|max:255',
                'experienceType' => 'required|in:Free,Paid',
                'pricePerGuest' => 'nullable|numeric|min:0|required_if:experienceType,Paid',
                'consent' => 'required|boolean|accepted',
            ]);

            // Map frontend camelCase keys to snake_case for database
            $mappedData = [
                'host_name' => $data['hostName'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'city' => $data['city'],
                'state' => $data['state'],
                'address' => $data['address'],
                'bride_name' => $data['brideName'],
                'groom_name' => $data['groomName'],
                'wedding_title' => $data['weddingTitle'],
                'wedding_description' => $data['weddingDescription'],
                'start_date' => Carbon::createFromFormat('Y-m-d', $data['startDate']),
                'end_date' => !empty($data['endDate']) ? Carbon::createFromFormat('Y-m-d', $data['endDate']) : null,
                'venue' => $data['venue'],
                'guests' => $data['guests'],
                'languages' => $data['languages'],
                'experience_type' => $data['experienceType'],
                'price_per_guest' => $data['pricePerGuest'],
                'consent' => $data['consent'],
            ];

            // Store in database
            return DB::transaction(function () use ($mappedData) {
                $event = WeddingEvent::create($mappedData);

                return response()->json([
                    'message' => 'Wedding event submitted successfully',
                    'data' => $event,
                ], 201);
            });

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('WeddingEvent Error: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'An error occurred while processing your request.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : 'Contact support.',
            ], 500);
        }
    }
}