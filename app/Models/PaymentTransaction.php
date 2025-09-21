<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'policy_id',
        'gateway',
        'amount_cents',
        'currency',
        'status',
        'external_ref',
        'paid_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(InsurancePlan::class, 'plan_id');
    }

    public function policy()
    {
        return $this->belongsTo(MemberPolicy::class, 'policy_id');
    }
}
