<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionTransaction extends Model
{
    protected $fillable = [
        'earner_user_id',
        'source_user_id',
        'plan_id',
        'payment_transaction_id',
        'level',
        'basis_amount_cents',
        'commission_cents',
        'status',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function earner()
    {
        return $this->belongsTo(User::class, 'earner_user_id');
    }

    public function source()
    {
        return $this->belongsTo(User::class, 'source_user_id');
    }

    public function plan()
    {
        return $this->belongsTo(InsurancePlan::class, 'plan_id');
    }

    public function payment()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
