<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PendingRegistration extends Model
{
    protected $fillable = [
        'registration_id',
        'agent_id',
        'plan_id',
        'clients_data',
        'amount_breakdown',
        'total_amount_cents',
        'currency',
        'status',
        'expires_at'
    ];

    protected $casts = [
        'clients_data' => 'array',
        'amount_breakdown' => 'array',
        'expires_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class, 'plan_id');
    }

    /**
     * Check if the registration has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Clean up expired registrations
     */
    public static function cleanupExpired(): int
    {
        return self::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }
}