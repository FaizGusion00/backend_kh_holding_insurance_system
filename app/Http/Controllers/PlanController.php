<?php

namespace App\Http\Controllers;

use App\Models\CommissionRate;
use App\Models\InsurancePlan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = InsurancePlan::where('active', true)->get()->map(function ($p) {
            $annual = (int) ($p->price_cents ?? 0); // cents
            $slug = $p->slug;
            $commitmentFee = (int) ($p->commitment_fee_cents ?? 0); // cents
            
            // Compute instalments correctly
            $monthly = $annual > 0 ? round($annual / 12) : 0;
            $quarterly = $annual > 0 ? round($annual / 4) : 0;
            $semi = $annual > 0 ? round($annual / 2) : 0;

            // Available modes: MediPlan Coop has only monthly/annually per spec
            $available = $slug === 'medical' ? ['monthly','annually'] : ['monthly','quarterly','semi_annually','annually'];

            return [
                'id' => $p->id,
                'name' => $p->name, // Changed from plan_name to name
                'plan_code' => $p->slug,
                'description' => $p->description,
                'annually_price' => $annual ? number_format($annual / 100, 2, '.', '') : null,
                'commitment_fee' => number_format($commitmentFee / 100, 2, '.', ''),
                'benefits' => [],
                'terms_conditions' => [],
                'min_age' => 0,
                'max_age' => 100,
                'is_active' => (bool) $p->active,
                'pricing' => [
                    'monthly' => ['base_price' => number_format($monthly / 100, 2, '.', '')],
                    'quarterly' => ['base_price' => $quarterly ? number_format($quarterly / 100, 2, '.', '') : null],
                    'semi_annually' => ['base_price' => $semi ? number_format($semi / 100, 2, '.', '') : null],
                    'annually' => ['base_price' => number_format($annual / 100, 2, '.', '')],
                ],
                'available_modes' => $available,
            ];
        });
        return response()->json(['status' => 'success', 'data' => $plans]);
    }

	public function show(int $id)
	{
		$plan = InsurancePlan::findOrFail($id);
		return response()->json(['status' => 'success', 'data' => ['plan' => $plan]]);
	}

	public function commissionRates(int $id)
	{
		$rates = CommissionRate::where('plan_id', $id)->orderBy('level')->get();
		return response()->json(['status' => 'success', 'data' => ['rates' => $rates]]);
	}
}


