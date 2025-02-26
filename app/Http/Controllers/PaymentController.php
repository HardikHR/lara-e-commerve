<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createOrder(Request $request)
    {
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        $orderData = [
            'receipt' => 'order_' . time(),
            'amount' => $request->amount * 100, // Convert to paisa
            'currency' => 'INR',
            'payment_capture' => 1 // Auto capture
        ];

        $order = $api->order->create($orderData);

        return response()->json([
            'order_id' => $order['id'],
            'razorpay_key' => env('RAZORPAY_KEY'),
            'amount' => $orderData['amount'],
            'currency' => 'INR'
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        $attributes = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature
        ];

        try {
            $api->utility->verifyPaymentSignature($attributes);
            
            // Save payment details to DB
            Order::create([
                'user_id' => auth()->id(),
                'order_id' => $request->razorpay_order_id,
                'payment_id' => $request->razorpay_payment_id,
                'amount' => $request->amount,
                'status' => 'Paid'
            ]);

            return response()->json(['message' => 'Payment successful']);
        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return response()->json(['message' => 'Payment verification failed'], 400);
        }
    }
}

