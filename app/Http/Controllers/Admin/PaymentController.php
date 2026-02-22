<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    // app/Http/Controllers/Admin/PaymentController.php
    public function index()
    {
        $payments = Payment::with(['user', 'plan'])->latest()->paginate(20);
        return view('admin.payments.index', compact('payments'));
    }

    public function verify(Payment $payment, Request $request)
    {
        $payment->status = $request->status; // completed or rejected
        $payment->verified_at = now();
        $payment->admin_note = $request->note;

        if ($request->status === 'completed') {
            $payment->user->subscription_plan_id = $payment->subscription_plan_id;
            $payment->user->save();
        }else {
            // Optionally, you can also clear the user's subscription if rejected
            $payment->user->subscription_plan_id = null;
            $payment->user->save();
        }

        $payment->save();

        return back()->with('success', 'Payment updated');
    }
}
