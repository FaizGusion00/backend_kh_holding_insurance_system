<?php

namespace Database\Seeders;

use App\Models\CommissionRate;
use App\Models\InsurancePlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommissionRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medical = InsurancePlan::where('slug', 'medical')->first();
        $gold = InsurancePlan::where('slug', 'senior-gold-270')->first();
        $diamond = InsurancePlan::where('slug', 'senior-diamond-370')->first();

        if ($medical) {
            $fixed = [1 => 1000, 2 => 200, 3 => 200, 4 => 100, 5 => 75]; // cents for RM10, RM2, RM2, RM1, RM0.75
            foreach ($fixed as $level => $amount) {
                CommissionRate::updateOrCreate([
                    'plan_id' => $medical->id,
                    'level' => $level,
                ], [
                    'fixed_amount_cents' => $amount,
                    'rate_percent' => null,
                ]);
            }
        }

        $percentGold = [1 => 11.11, 2 => 2.22, 3 => 2.22, 4 => 1.11, 5 => 0.83];
        if ($gold) {
            foreach ($percentGold as $level => $pct) {
                CommissionRate::updateOrCreate([
                    'plan_id' => $gold->id,
                    'level' => $level,
                ], [
                    'rate_percent' => $pct,
                    'fixed_amount_cents' => null,
                ]);
            }
        }

        $percentDiamond = [1 => 11.11, 2 => 2.22, 3 => 2.22, 4 => 1.11, 5 => 0.83];
        if ($diamond) {
            foreach ($percentDiamond as $level => $pct) {
                CommissionRate::updateOrCreate([
                    'plan_id' => $diamond->id,
                    'level' => $level,
                ], [
                    'rate_percent' => $pct,
                    'fixed_amount_cents' => null,
                ]);
            }
        }
    }
}
