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
        'followup_number',
        'assigned_to',
        'followup_date',
        'followup_time',
        'priority',
        'status',
        'notes',
        'completed_at',
        'created_by',

        // Outcome fields — filled when marked complete
        'outcome_final_status',
        'outcome_status',
        'outcome_interest',
        'outcome_notes',
        'next_action',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'completed_at'  => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    // ── Boot: auto-set followup_number on creation ────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (EduLeadFollowup $followup) {
            if (empty($followup->followup_number)) {
                $followup->followup_number = (static::where('edu_lead_id', $followup->edu_lead_id)
                    ->max('followup_number') ?? 0) + 1;
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────

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

    // ── Scopes ────────────────────────────────────────────────────────

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

    // ── Helpers ───────────────────────────────────────────────────────

    public function getOrdinalLabelAttribute(): string
    {
        $n = (int) ($this->followup_number ?? 0);

        if ($n === 0) {
            // Fallback: exclude soft deleted when counting position
            $n = static::where('edu_lead_id', $this->edu_lead_id)
                    ->where('id', '<=', $this->id)
                    ->count();
        }

        $suffix = match($n % 10) {
            1 => $n % 100 === 11 ? 'th' : 'st',
            2 => $n % 100 === 12 ? 'th' : 'nd',
            3 => $n % 100 === 13 ? 'th' : 'rd',
            default => 'th',
        };

        return $n . $suffix;
    }

}
