<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VisaInquiryEmail;
use App\Models\VisaInquiry;
use Twilio\Rest\Client;

class VisaInquiryController extends Controller
{
    public function store(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
            'visa_type' => 'required|string|max:100',
            'message' => 'nullable|string',
        ]);

        // Save inquiry to database
        try {
            $inquiry = VisaInquiry::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'visa_type' => $validated['visa_type'],
                'message' => $validated['message'] ?? '',
                'status' => 'new',
            ]);
        } catch (\Exception $e) {
            \Log::error('Database save failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save inquiry'], 500);
        }

        // Send email to customer
        try {
            Mail::to($validated['email'])->send(new VisaInquiryEmail($inquiry->toArray()));
        } catch (\Exception $e) {
            \Log::error('Customer email sending failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send customer email'], 500);
        }

        // Send email to admin
        try {
            Mail::to(env('ADMIN_EMAIL'))->send(new VisaInquiryEmail($inquiry->toArray()));
        } catch (\Exception $e) {
            \Log::error('Admin email sending failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send admin email'], 500);
        }

        // Send WhatsApp message to customer
        try {
            $this->sendWhatsAppMessage($validated['phone'], $inquiry->toArray());
        } catch (\Exception $e) {
            \Log::error('Customer WhatsApp sending failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send customer WhatsApp message'], 500);
        }

        // Send WhatsApp message to admin
        try {
            $this->sendWhatsAppMessage(env('ADMIN_WHATSAPP_NUMBER'), $inquiry->toArray(), true);
        } catch (\Exception $e) {
            \Log::error('Admin WhatsApp sending failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send admin WhatsApp message'], 500);
        }

        return response()->json([
            'message' => 'Inquiry submitted successfully',
            'data' => $inquiry
        ], 201);
    }

    private function sendWhatsAppMessage($phone, $data, $isAdmin = false)
    {
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        if ($isAdmin) {
            $messageBody = "New Visa Inquiry Received!\n";
            $messageBody .= "Name: {$data['name']}\n";
            $messageBody .= "Email: {$data['email']}\n";
            $messageBody .= "Phone: {$data['phone']}\n";
            $messageBody .= "Visa Type: {$data['visa_type']}\n";
            $messageBody .= "Message: {$data['message']}\n";
            $messageBody .= "Please review and respond promptly.";
        } else {
            $messageBody = "Thank you for your visa inquiry, {$data['name']}!\n";
            $messageBody .= "Visa Type: {$data['visa_type']}\n";
            $messageBody .= "We'll contact you soon regarding your request.";
        }

        $twilio->messages->create(
            "whatsapp:" . $this->formatPhoneNumber($phone),
            [
                'from' => 'whatsapp:' . env('TWILIO_WHATSAPP_NUMBER'),
                'body' => $messageBody
            ]
        );
    }

    private function formatPhoneNumber($phone)
    {
        // Ensure phone number starts with +
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }
}