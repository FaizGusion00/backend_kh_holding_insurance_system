<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsurancePlan extends Model
{
    protected $fillable = [
        'price_cents',
        'commitment_fee_cents',
        'name',
        'slug',
        'description',
        'monthly_price',
        'quarterly_price',
        'semi_annually_price',
        'annually_price',
        'commitment_fee',
        'room_board_limit',
        'annual_limit',
        'government_cash_allowance',
        'death_benefit',
        'min_age',
        'max_age',
        'renewal_age',
        'benefits',
        'terms_conditions',
        'waiting_period_general',
        'waiting_period_specific',
        'administrator',
        'panel_hospitals',
        'panel_clinics',
        'is_active',
    ];

    public function commissionRates()
    {
        return $this->hasMany(CommissionRate::class, 'plan_id');
    }
}
