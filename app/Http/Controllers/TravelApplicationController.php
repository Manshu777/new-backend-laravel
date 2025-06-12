<?php

namespace App\Http\Controllers;

use App\Models\TravelApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Exception;

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
            'passport_expiry_date' => 'nullable|date_format:d/m/Y',
            'tentative_departure_date' => 'required|date_format:d/m/Y',
            'tentative_return_date' => 'nullable|date_format:d/m/Y|after:tentative_departure_date',
            'destination_country' => 'string|max:100',
            'purpose_of_visit' => 'string|max:255',
            'date_of_birth' => 'required|date_format:d/m/Y',
            'gender' => 'string|in:male,female,other',
            'address' => 'string',
            'city' => 'string',
            'state' => 'string',
            'pincode' => 'string|max:10',
            'service_type' => 'nullable|string',
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
                $file = $request->file($field);
                if (!$file->isValid()) {
                    return response()->json([
                        'message' => "Invalid file uploaded for {$field}.",
                    ], 422);
                }
                $path = $file->store('documents', 'public');
                if (!$path) {
                    return response()->json([
                        'message' => "Failed to store {$field} file.",
                    ], 500);
                }
                $paths[$field . '_path'] = $path;
            }
        }

        // Parse dates
        $dateFields = [
            'tentative_departure_date' => true,
            'tentative_return_date' => false,
            'passport_expiry_date' => false,
            'date_of_birth' => true,
        ];
        foreach ($dateFields as $field => $required) {
            if ($required || !empty($data[$field])) {
                try {
                    $data[$field] = Carbon::createFromFormat('d/m/Y', $data[$field]);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => "Invalid date format for {$field}. Use d/m/Y.",
                    ], 422);
                }
            } else {
                $data[$field] = null;
            }
        }

        // Merge paths into data
        $data = array_merge($data, $paths);

        // Prepare email data
        $emailData = [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],


        ];

        // Use database transaction
        return DB::transaction(function () use ($data, $emailData) {
            $application = TravelApplication::create($data);

            // Send email
            try {
                if (!view()->exists('emails.travel_application')) {
                    Log::warning('Email template emails.travel_application not found.');
                } else {
                    Mail::send('emails.travel_application', $emailData, function ($message) {
                        $message->to('vishal@nextgentrip.com')
                                ->subject('New Travel Application Submission');
                    });
                }
            } catch (Exception $e) {
                Log::error('Email sending failed: ' . $e->getMessage(), [
                    'stack' => $e->getTraceAsString(),
                    'data' => $emailData,
                ]);
            }

            return response()->json([
                'message' => 'Travel application submitted successfully',
                'data' => $application,
            ], 201);
        });

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);

    } catch (QueryException $e) {
        Log::error('Database error: ' . $e->getMessage(), [
            'stack' => $e->getTraceAsString(),
            'request_data' => $request->all(),
        ]);
        return response()->json([
            'message' => 'Database error occurred.',
            'error' => env('APP_DEBUG', false) ? $e->getMessage() : 'Contact support.',
        ], 500);

    } catch (Exception $e) {
        Log::error('TravelApplication Error: ' . $e->getMessage(), [
            'stack' => $e->getTraceAsString(),
            'request_data' => $request->all(),
        ]);
        return response()->json([
            'message' => 'An error occurred while processing your request.',
            'error' => env('APP_DEBUG', false) ? $e->getMessage() : 'Contact support for assistance.',
        ], 500);
    }
}
 
}