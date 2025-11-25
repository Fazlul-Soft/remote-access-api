<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
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
}
