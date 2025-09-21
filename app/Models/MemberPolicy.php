<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPolicy extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'policy_number',
        'start_date',
        'end_date',
        'status',
        'auto_renew',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(InsurancePlan::class, 'plan_id');
    }
}
