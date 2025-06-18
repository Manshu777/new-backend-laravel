<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RazorpayOrderController extends Controller
{


    public function capturePayment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'payment_id' => 'required|string',
        'amount' => 'required|numeric|min:100',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        $api = new Api('rzp_test_heUFNPhcTPl901', 'MOFh3dbTt5z1553YYoN8obhl');

        $payment = $api->payment->fetch($request->payment_id);

        $payment->capture(['amount' => intval(round($request->amount * 100))]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment captured successfully',
        ], 200);

    } catch (\Exception $e) {
        Log::error('Razorpay capture failed: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to capture payment',
            'error' => $e->getMessage(),
        ], 500);
    }
}



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
              $api = new Api('rzp_test_heUFNPhcTPl901', 'MOFh3dbTt5z1553YYoN8obhl');

            // Create Razorpay order
            $orderData = [
                'amount' => intval(round($request->amount * 100)),
                'currency' => $request->currency,
                'receipt' => $request->receipt,
                'payment_capture' => 0, // Auto-capture payment
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
}