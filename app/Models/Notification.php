<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'priority',
        'category',
        'related_user_id',
        'related_model_type',
        'related_model_id',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    public function relatedModel(): MorphTo
    {
        return $this->morphTo('related_model', 'related_model_type', 'related_model_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Methods
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    // Static methods for creating notifications
    public static function createForUser($userId, $type, $title, $message, $data = null, $priority = 'normal', $category = 'general')
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'category' => $category,
        ]);
    }

    public static function createNetworkNotification($userId, $relatedUserId, $title, $message, $data = null)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'new_network_member',
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => 'normal',
            'category' => 'network',
            'related_user_id' => $relatedUserId,
        ]);
    }

    public static function createCommissionNotification($userId, $amount, $source, $data = null)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'commission_earned',
            'title' => 'Commission Earned',
            'message' => "You earned RM{$amount} commission from {$source}",
            'data' => array_merge(['amount' => $amount, 'source' => $source], $data ?: []),
            'priority' => 'normal',
            'category' => 'commission',
        ]);
    }

    public static function createPaymentNotification($userId, $amount, $status, $data = null)
    {
        $title = $status === 'completed' ? 'Payment Received' : 'Payment Update';
        $message = $status === 'completed' 
            ? "Payment of RM{$amount} has been successfully processed"
            : "Payment of RM{$amount} status updated to {$status}";

        return static::create([
            'user_id' => $userId,
            'type' => 'payment_update',
            'title' => $title,
            'message' => $message,
            'data' => array_merge(['amount' => $amount, 'status' => $status], $data ?: []),
            'priority' => $status === 'failed' ? 'high' : 'normal',
            'category' => 'payment',
        ]);
    }

    public static function createReminderNotification($userId, $type, $title, $message, $dueDate = null)
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $dueDate ? ['due_date' => $dueDate] : null,
            'priority' => 'high',
            'category' => 'reminder',
        ]);
    }
}
