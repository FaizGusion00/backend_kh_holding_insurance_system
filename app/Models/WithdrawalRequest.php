<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount_cents',
        'status',
        'approved_by',
        'paid_at',
        'bank_meta',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'bank_meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
