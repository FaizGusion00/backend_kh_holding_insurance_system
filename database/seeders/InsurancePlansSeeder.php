<?php

namespace Database\Seeders;

use App\Models\InsurancePlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsurancePlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update plan pricing to match provided specs (price_cents is annual)
        $plans = [
            [
                'slug' => 'medical',
                'name' => 'MediPlan Coop',
                'uses_percentage_commission' => false,
                'active' => true,
                'price_cents' => 108000, // RM 1,080 annually (RM 90 monthly)
                'commitment_fee_cents' => 0, // No commitment fee for MediPlan Coop
            ],
            [
                'slug' => 'senior-gold-270',
                'name' => 'Senior Care Plan Gold 270',
                'uses_percentage_commission' => true,
                'active' => true,
                'price_cents' => 180000, // RM 1,800 annually (RM 150 monthly)
                'commitment_fee_cents' => 15000, // RM 150 commitment fee (separate from monthly)
            ],
            [
                'slug' => 'senior-diamond-370',
                'name' => 'Senior Care Plan Diamond 370',
                'uses_percentage_commission' => true,
                'active' => true,
                'price_cents' => 252000, // RM 2,520 annually (RM 210 monthly)
                'commitment_fee_cents' => 21000, // RM 210 commitment fee (separate from monthly)
            ],
        ];

        foreach ($plans as $plan) {
            InsurancePlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
