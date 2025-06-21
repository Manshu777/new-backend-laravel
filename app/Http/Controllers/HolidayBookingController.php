<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\HolidayBooking;
use App\Mail\UserBookingConfirmation;
use App\Mail\OwnerBookingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class HolidayBookingController extends Controller
{
    public function bookHoliday(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'holiday_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create booking
        $booking = HolidayBooking::create([
            'holiday_name' => $request->holiday_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'message' => $request->message,
        ]);

        try {
            // Send email to user
            Log::info('Sending user confirmation to: ' . $booking->email);
            Mail::to($booking->email)->send(new UserBookingConfirmation($booking));

            // Send email to owner
            $ownerEmail = 'hsrana.hr@gmail.com';
            if (!empty($ownerEmail)) {
                Log::info('Sending owner notification to: ' . $ownerEmail);
                Mail::to($ownerEmail)->send(new OwnerBookingNotification($booking));
            } else {
                Log::error('Owner email is not configured in MAIL_OWNER_EMAIL');
                return response()->json([
                    'status' => 'partial_success',
                    'message' => 'Booking created, but owner notification failed due to missing MAIL_OWNER_EMAIL',
                    'data' => $booking
                ], 201);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'data' => $booking
            ], 201);

        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'partial_success',
                'message' => 'Booking created, but email sending failed',
                'data' => $booking,
                'error' => $e->getMessage()
            ], 201);
        }
    }
}