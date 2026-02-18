<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EduLeadFollowup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'edu_lead_id',
        'assigned_to',
        'followup_date',
        'followup_time',
        'priority',
        'status',
        'notes',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function eduLead(): BelongsTo
    {
        return $this->belongsTo(EduLead::class, 'edu_lead_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('followup_date', today());
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->whereDate('followup_date', '<', today());
    }
}
