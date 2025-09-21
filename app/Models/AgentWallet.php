<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance_cents',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }
}
