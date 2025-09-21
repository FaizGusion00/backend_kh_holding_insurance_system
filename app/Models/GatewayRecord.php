<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayRecord extends Model
{
    protected $fillable = [
        'provider',
        'direction',
        'external_ref',
        'payload',
        'status_code',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
