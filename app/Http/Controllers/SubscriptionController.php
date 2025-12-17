<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PaymentDetails;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = SubscriptionPlan::all();
        $paymentMethods = PaymentDetails::where('is_active', true)->get();

        return response()->json([
            'plans' => $plans,
            'payment_methods' => $paymentMethods,
        ]);
    }

    public function paymentMethod()
    {
        return PaymentDetails::all();
    }
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'amount' => 'required|numeric',
            'transaction_id' => 'required|string',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        // Create payment record
        $payment = Payment::create([
            'uuid' => strtoupper(Str::random(10)),
            'user_id' => Auth::user()->id,
            'subscription_plan_id' => $plan->id,
            'amount' => $request->amount,
            'transaction_id' => $request->transaction_id,
            'status' => 'under_review',
        ]);

        return response()->json([
            'message' => 'Payment submitted! Admin will verify soon.',
            'payment_id' => $payment->uuid,
            'status' => 'under_review'
        ]);
    }
}
