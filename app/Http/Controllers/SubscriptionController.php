<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function plans()
    {
        return SubscriptionPlan::all();
    }

    public function subscribe(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:subscription_plans,id']);

        $user = Auth::user();
        $user->subscription_plan_id = $request->plan_id;
        $user->save();

        return response()->json(['message' => 'Subscribed successfully']);
    }
}
