<?php

namespace App\Http\Controllers;

use App\Models\WeddingExperiences;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WeddingJoinerController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validate request
            $data = $request->validate([
                'fullName' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:15',
                'country' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'nationality' => 'nullable|string|max:100',
                'age' => 'nullable|integer|min:0',
                'gender' => 'nullable|in:Male,Female,Other',
                'experienceReason' => 'nullable|string',
                'attendedBefore' => 'nullable|in:Yes,No',
                'interests' => 'nullable|array',
                'availableMonths' => 'nullable|array',
                'regionPreference' => 'nullable|string|max:255',
                'stayDuration' => 'nullable|string|max:100',
                'travelArranged' => 'nullable|in:Yes,No',
                'needAssistance' => 'nullable|in:Yes,No',
                'paidExperience' => 'nullable|in:Free,Paid',
                'budget' => 'nullable|numeric|min:0',
                'consent' => 'required|boolean|accepted',
                'promoConsent' => 'nullable|boolean',
            ]);

            // Map frontend camelCase keys to snake_case for database
            $mappedData = [
                'full_name' => $data['fullName'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'country' => $data['country'],
                'city' => $data['city'],
                'nationality' => $data['nationality'],
                'age' => $data['age'],
                'gender' => $data['gender'],
                'experience_reason' => $data['experienceReason'],
                'attended_before' => $data['attendedBefore'],
                'interests' => $data['interests'] ? json_encode($data['interests']) : null,
                'available_months' => $data['availableMonths'] ? json_encode($data['availableMonths']) : null,
                'region_preference' => $data['regionPreference'],
                'stay_duration' => $data['stayDuration'],
                'travel_arranged' => $data['travelArranged'],
                'need_assistance' => $data['needAssistance'],
                'paid_experience' => $data['paidExperience'],
                'budget' => $data['budget'],
                'consent' => $data['consent'],
                'promo_consent' => $data['promoConsent'] ?? false,
            ];

            // Store in database
            return DB::transaction(function () use ($mappedData) {
                $experience = WeddingExperiences::create($mappedData);

                return response()->json([
                    'message' => 'Wedding joiner  submitted successfully',
                    'data' => $experience,
                ], 201);
            });

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('WeddingExperiences Error: ' . $e->getMessage(), [
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