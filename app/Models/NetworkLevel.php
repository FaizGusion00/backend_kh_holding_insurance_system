<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkLevel extends Model
{
    protected $fillable = [
        'user_id',
        'agent_code',
        'referrer_code',
        'level',
        'root_agent_code',
        'level_path',
        'direct_downlines_count',
        'total_downlines_count',
        'commission_earned',
        'active_policies_count',
        'last_updated'
    ];

    protected $casts = [
        'level_path' => 'array',
        'last_updated' => 'datetime',
        'commission_earned' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_code', 'agent_code');
    }

    public function rootAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'root_agent_code', 'agent_code');
    }

    /**
     * Get all downlines at a specific level
     */
    public function getDownlinesAtLevel($level)
    {
        return self::where('root_agent_code', $this->root_agent_code)
            ->where('level', $level)
            ->where('level_path', 'like', '%' . $this->agent_code . '%')
            ->get();
    }

    /**
     * Get direct downlines (next level)
     */
    public function getDirectDownlines()
    {
        return self::where('root_agent_code', $this->root_agent_code)
            ->where('level', $this->level + 1)
            ->where('referrer_code', $this->agent_code)
            ->get();
    }

    /**
     * Get all downlines (all levels below)
     */
    public function getAllDownlines()
    {
        return self::where('root_agent_code', $this->root_agent_code)
            ->where('level', '>', $this->level)
            ->where('level_path', 'like', '%' . $this->agent_code . '%')
            ->get();
    }
}