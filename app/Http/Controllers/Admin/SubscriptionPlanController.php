<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PaymentDetails;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::latest()->get();
        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.subscription-plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_devices' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
            'grace_period_days' => 'nullable|integer',
            'hide_data_after_days' => 'nullable|integer',
        ]);

        SubscriptionPlan::create($request->all());

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan created successfully');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.edit', compact('subscriptionPlan'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_devices' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $subscriptionPlan->update($request->all());

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Plan updated successfully');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->delete();
        return back()->with('success', 'Plan deleted');
    }

    // payment methods
    public function paymentMethods()
    {
        $paymentMethods = PaymentDetails::all();
        return view('admin.subscription-plans.payment-methods', compact('paymentMethods'));
    }
    public function paymentMethodCreate()
    {
        return view('admin.subscription-plans.payment-method-form');
    }
    public function paymentMethodAdd(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string|max:255',
            'merchant_no'    => 'required|string|max:255',
            'details'        => 'nullable|string',
            'note'           => 'nullable|string',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $data['logo'] = $filename;
        }

        PaymentDetails::create($data);

        return redirect()->route('admin.payment-methods')->with('success', 'Payment method added successfully');
    }

    public function paymentMethodEdit(PaymentDetails $paymentDetails)
    {
        return view('admin.subscription-plans.payment-method-form', compact('paymentDetails'));
    }

    public function paymentMethodUpdate(Request $request, PaymentDetails $paymentDetails)
    {
        $request->validate([
            'payment_method' => 'required|string|max:255',
            'merchant_no'    => 'required|string|max:255',
            'details'        => 'nullable|string',
            'note'           => 'nullable|string',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $paymentDetails->payment_method = $request->payment_method ?? $paymentDetails->payment_method;
        $paymentDetails->merchant_no = $request->merchant_no ?? $paymentDetails->merchant_no;
        $paymentDetails->details = $request->details ?? $paymentDetails->details;
        $paymentDetails->note = $request->note ?? $paymentDetails->note;
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($paymentDetails->logo && file_exists(public_path('uploads/' . $paymentDetails->logo))) {
                unlink(public_path('uploads/' . $paymentDetails->logo));
            }

            $file = $request->file('logo');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $paymentDetails->logo = $filename;
        }

        $paymentDetails->save();

        return redirect()->route('admin.payment-methods')->with('success', 'Payment method updated successfully');
    }

    public function paymentMethodDelete(PaymentDetails $paymentDetails)
    {
        if ($paymentDetails->logo && file_exists(public_path('uploads/' . $paymentDetails->logo))) {
            unlink(public_path('uploads/' . $paymentDetails->logo));
        }
        $paymentDetails->delete();

        return back()->with('success', 'Payment method deleted successfully');
    }
}
