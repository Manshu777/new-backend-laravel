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
                'service_type' => 'required|string',
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
                    try {
                        $paths[$field . '_path'] = $request->file($field)->store('documents', 'public');
                        if (!$paths[$field . '_path']) {
                            throw new Exception("Failed to store {$field} file.");
                        }
                    } catch (Exception $e) {
                        Log::error("File upload error for {$field}: " . $e->getMessage());
                        return response()->json([
                            'message' => "Failed to upload {$field}.",
                        ], 500);
                    }
                }
            }

            // Format dates to Carbon instances
            $data['tentative_departure_date'] = Carbon::createFromFormat('d/m/Y', $data['tentative_departure_date']);

            if (!empty($data['tentative_return_date'])) {
                $data['tentative_return_date'] = Carbon::createFromFormat('d/m/Y', $data['tentative_return_date']);
            } else {
                $data['tentative_return_date'] = null;
            }

            if (!empty($data['passport_expiry_date'])) {
                $data['passport_expiry_date'] = Carbon::createFromFormat('d/m/Y', $data['passport_expiry_date']);
            } else {
                $data['passport_expiry_date'] = null;
            }

            $data['date_of_birth'] = Carbon::createFromFormat('d/m/Y', $data['date_of_birth']);

            // Merge paths into data
            $data = array_merge($data, $paths);

            // Prepare email data
            $emailData = [
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'service_type' => $data['service_type'],
                'destination_country' => $data['destination_country'],
                'purpose_of_visit' => $data['purpose_of_visit'],
                // Add other fields as needed for the email template
            ];

            // Use database transaction for consistency
            return DB::transaction(function () use ($data, $emailData) {
                // Store into DB
                $application = TravelApplication::create($data);

                // Send email
                try {
                    Mail::send('emails.travel_application', $emailData, function ($message) {
                        $message->to('vishal@nextgentrip.com')
                                ->subject('New Travel Application Submission');
                    });
                } catch (Exception $e) {
                    Log::error('Email sending failed: ' . $e->getMessage(), [
                        'stack' => $e->getTraceAsString(),
                        'data' => $emailData,
                    ]);
                    // Optionally, decide whether to fail the transaction or continue
                    // For now, we'll log and continue
                }

                return response()->json([
                    'message' => 'Travel application submitted successfully',
                    'data' => $application,
                ], 201);
            });

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            // Log the actual error for debugging
            Log::error('TravelApplication Error: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'An error occurred while processing your request.',
            ], 500);
        }
    }
}