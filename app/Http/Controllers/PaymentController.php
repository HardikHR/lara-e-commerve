<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function createOrder(Request $request, Order $Order)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount * 100,
            'currency' => 'INR',
        ]);

        $Order->amount = $request->amount;
        $Order->payment_id = $paymentIntent->id;
        $Order->user_id = Auth::id();
        $Order->order_id = $paymentIntent->client_secret;
        $Order->save();

        return response()->json(['data' => $Order]);
    }

    public function verifyPayment(Request $request)
    {
        $paymentId = $request->payment_id;
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $payment = \Stripe\PaymentIntent::retrieve($paymentId);
        if ($payment->status == 'succeeded') {
            return response()->json(['message' => 'Payment Successful']);
        } else {
            return response()->json(['message' => 'Payment Failed']);
        }
    }
}

