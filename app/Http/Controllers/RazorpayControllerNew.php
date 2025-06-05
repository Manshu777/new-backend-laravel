<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RazorpayControllerNew extends Controller
{
     public function verifyPayment(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        // Razorpay secret key (replace with your actual secret key)
        $secret = env('RAZORPAY_SECRET', 'your_razorpay_secret_key');

        // Generate signature
        $generatedSignature = hash_hmac('sha256', $request->razorpay_order_id . '|' . $request->razorpay_payment_id, $secret);

        // Compare signatures
        if ($generatedSignature === $request->razorpay_signature) {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified'
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed'
            ], 400);
        }
    }

    public function initiateRazorpayRefund(Request $request)
    {
        try {
            $validated = $request->validate([
                'razorpay_payment_id' => 'required|string',
                'amount' => 'required|numeric|min:1',
            ]);

            $refund = $this->razorpay->payment
                ->fetch($validated['razorpay_payment_id'])
                ->refund([
                    'amount' => $validated['amount'], // Amount in paise
                    'speed' => 'normal', // Use 'instant' if supported
                ]);

            return response()->json([
                'status' => 'success',
                'refund' => [
                    'id' => $refund->id,
                    'amount' => $refund->amount,
                    'currency' => $refund->currency,
                    'payment_id' => $refund->payment_id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay refund failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'error' => 'Refund initiation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
