<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRate extends Model
{
    protected $fillable = [
        'plan_id',
        'level',
        'rate_percent',
        'fixed_amount_cents',
    ];

    public function plan()
    {
        return $this->belongsTo(InsurancePlan::class, 'plan_id');
    }
}
