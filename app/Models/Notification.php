<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'title',
        'message',
        'sender_id',
        'sender_type',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    /**
     * Get the recipient (patient, hospital, or admin).
     */
    public function recipient()
    {
        return match ($this->user_type) {
            'patient' => $this->belongsTo(Patient::class, 'user_id'),
            'hospital' => $this->belongsTo(Hospital::class, 'user_id'),
            'admin' => $this->belongsTo(Admin::class, 'user_id'),
            default => null,
        };
    }

    /**
     * Get the sender (patient, hospital, or admin).
     */
    public function sender()
    {
        return match ($this->sender_type) {
            'patient' => $this->belongsTo(Patient::class, 'sender_id'),
            'hospital' => $this->belongsTo(Hospital::class, 'sender_id'),
            'admin' => $this->belongsTo(Admin::class, 'sender_id'),
            default => null,
        };
    }

    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope to get notifications for a specific user.
     */
    public function scopeForUser($query, $userId, $userType)
    {
        return $query->where('user_id', $userId)->where('user_type', $userType);
    }
}
