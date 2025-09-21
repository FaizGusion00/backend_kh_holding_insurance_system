<?php

namespace App\Http\Controllers;

use App\Models\InsurancePlan;
use App\Models\MemberPolicy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function index()
    {
        $items = MemberPolicy::where('user_id', auth('api')->id())->latest()->paginate(15);
        return response()->json(['status' => 'success', 'data' => $items]);
    }

    public function show(int $id)
    {
        $policy = MemberPolicy::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => ['policy' => $policy]]);
    }

    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'insurance_plan_id' => 'required|exists:insurance_plans,id',
            'payment_mode' => 'required|in:monthly,quarterly,semi_annually,annually',
        ]);
        $plan = InsurancePlan::findOrFail($validated['insurance_plan_id']);
        $policy = MemberPolicy::create([
            'user_id' => auth('api')->id(),
            'plan_id' => $plan->id,
            'policy_number' => 'POL'.now()->format('YmdHis').auth('api')->id(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'pending',
        ]);
        return response()->json(['status' => 'success', 'data' => ['policy' => $policy]]);
    }
}


