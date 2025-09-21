<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'postal_code',
        'phone_number',
        'is_panel',
        'is_active',
    ];

    protected $casts = [
        'is_panel' => 'boolean',
        'is_active' => 'boolean',
    ];
}