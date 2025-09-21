<?php

namespace App\Http\Controllers;

use App\Models\CommissionRate;
use App\Models\InsurancePlan;
use Illuminate\Http\Request;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = InsurancePlan::with('commissionRates')->paginate(15);
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:insurance_plans,slug',
            'description' => 'nullable|string',
            'price_cents' => 'required|integer|min:0',
            'uses_percentage_commission' => 'boolean',
            'active' => 'boolean',
        ]);

        $plan = InsurancePlan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully');
    }

    public function show(InsurancePlan $plan)
    {
        $plan->load('commissionRates');
        return view('admin.plans.show', compact('plan'));
    }

    public function edit(InsurancePlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, InsurancePlan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:insurance_plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price_cents' => 'required|integer|min:0',
            'uses_percentage_commission' => 'boolean',
            'active' => 'boolean',
        ]);

        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully');
    }

    public function destroy(InsurancePlan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted successfully');
    }

    public function commissionRates(InsurancePlan $plan)
    {
        $rates = $plan->commissionRates()->orderBy('level')->get();
        return view('admin.plans.commission-rates', compact('plan', 'rates'));
    }

    public function updateCommissionRates(Request $request, InsurancePlan $plan)
    {
        $data = $request->validate([
            'rates' => 'required|array|size:5',
            'rates.*.level' => 'required|integer|between:1,5',
            'rates.*.rate_percent' => 'nullable|numeric|min:0|max:100',
            'rates.*.fixed_amount_cents' => 'nullable|integer|min:0',
        ]);

        foreach ($data['rates'] as $rateData) {
            CommissionRate::updateOrCreate(
                ['plan_id' => $plan->id, 'level' => $rateData['level']],
                [
                    'rate_percent' => $rateData['rate_percent'],
                    'fixed_amount_cents' => $rateData['fixed_amount_cents'],
                ]
            );
        }

        return redirect()->route('admin.plans.commission-rates', $plan)->with('success', 'Commission rates updated successfully');
    }
}
