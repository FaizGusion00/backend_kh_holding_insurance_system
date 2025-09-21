<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'source',
        'amount_cents',
        'commission_transaction_id',
        'withdrawal_request_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(AgentWallet::class, 'wallet_id');
    }
}
