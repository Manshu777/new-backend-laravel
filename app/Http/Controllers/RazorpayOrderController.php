<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\RazorpayTransaction;

class RazorpayOrderController extends Controller
{
    public function createOrder(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100', 
            'currency' => 'required|string|in:INR',
            'receipt' => 'required|string|max:40', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Initialize Razorpay API
            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

            // Create Razorpay order
            $orderData = [
                'amount' => $request->amount, // Amount in paise
                'currency' => $request->currency,
                'receipt' => $request->receipt,
                'payment_capture' => 1, // Auto-capture payment
            ];

            $order = $api->order->create($orderData);

            // Return order ID to frontend
            return response()->json([
                'status' => 'success',
                'order_id' => $order->id,
            ], 200);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Razorpay order creation failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order. Please try again.',
            ], 500);
        }
    }

     public function updatePayment(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'pnr' => 'required|string|max:255',
            'razorpay_payment_id' => 'required|string|max:255',
            'order_id' => 'required|string|max:255',
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Initialize Razorpay API
            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

            // Verify payment
            $payment = $api->payment->fetch($request->razorpay_payment_id);

            if ($payment->status !== 'captured') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not captured',
                ], 400);
            }

            // Fetch order to get amount and other details
            $order = $api->order->fetch($request->order_id);

            // Store transaction details in razorpay_transactions table
            $transaction = RazorpayTransaction::create([
                'order_id' => $request->order_id,
                'transaction_id' => $request->razorpay_payment_id,
                'amount' => $order->amount / 100, // Convert paise to rupees
                'currency' => $order->currency,
                'receipt' => $order->receipt,
                'user_name' => $request->user_name ?? 'Unknown',
                'user_email' => $request->user_email ?? 'unknown@example.com',
                'user_phone' => $request->user_phone ?? '0000000000',
                'status' => 'success',
            ]);

            // Here you can add logic to update the flight booking with the PNR
            // For example, update a bookings table with payment status
            // Assuming you have a Booking model
            // Booking::where('pnr', $request->pnr)->update([
            //     'payment_status' => 'success',
            //     'razorpay_transaction_id' => $request->razorpay_payment_id,
            // ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment updated successfully',
                'transaction_id' => $transaction->transaction_id,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Payment update failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update payment. Please try again.',
            ], 500);
        }
    }

}