<?php

namespace App\Http\Controllers;

use App\Models\TravelApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Mail;
class TravelApplicationController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validate request
            $data = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:15',
                'passport_number' => 'string|max:20',
                'passport_expiry_date' => 'nullable|date_format:d/m/Y', // Make nullable
                'tentative_departure_date' => 'date_format:d/m/Y',
                'tentative_return_date' => 'nullable|date_format:d/m/Y',
                'destination_country' => 'string|max:100',
                'purpose_of_visit' => 'string|max:255',
                'date_of_birth' => 'required|date_format:d/m/Y',
                'gender' => 'string',
                'address' => 'string',
                'city' => 'string',
                'state' => 'string',
                'pincode' => 'string|max:10',
                'service_type'=> 'required|string',
                'passport_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'passport_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'photograph' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'supporting_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ]);

            // Handle file uploads
            $paths = [];
            $files = ['passport_front', 'passport_back', 'photograph', 'supporting_document'];

            foreach ($files as $field) {
                if ($request->hasFile($field)) {
                    $paths[$field . '_path'] = $request->file($field)->store('documents', 'public');
                }
            }

            
            // Format dates to Carbon instances
            $data['tentative_departure_date'] = Carbon::createFromFormat('d/m/Y', $data['tentative_departure_date']);

            if (!empty($data['tentative_return_date'])) {
                $data['tentative_return_date'] = Carbon::createFromFormat('d/m/Y', $data['tentative_return_date']);
            } else {
                $data['tentative_return_date'] = null;
            }

            // Handle passport_expiry_date
            if (!empty($data['passport_expiry_date'])) {
                $data['passport_expiry_date'] = Carbon::createFromFormat('d/m/Y', $data['passport_expiry_date']);
            } else {
                $data['passport_expiry_date'] = null; // Set to null if not provided
            }

            $data['date_of_birth'] = Carbon::createFromFormat('d/m/Y', $data['date_of_birth']);

            // Merge paths into data
            $data = array_merge($data, $paths);


            Mail::send('emails.travel_application', $emailData, function ($message) {
                $message->to('vishal@nextgentrip.com')
                        ->subject('New Travel Application Submission');
            });

            // Store into DB
            $application = TravelApplication::create($data);

            return response()->json([
                'message' => 'Travel application submitted successfully',
                'data' => $application,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            // Log the actual error for debugging
            Log::error('TravelApplication Error: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'request_data' => $request->all(), // Log request data for context
            ]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}