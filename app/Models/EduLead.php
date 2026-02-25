<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EduLead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_code',
        'created_by',
        'assigned_to',
        'branch_id',
        'lead_source_id',
        'course_id',
        'program_id',
        'name',
        'email',
        'phone',
        'whatsapp_number',
        'description',
        'preferred_state',

        // Agent
        'agent_name',
        'referral_name',

        // Institution
        'institution_type',
        'school',
        'school_department',
        'college',
        'college_department',

        // Programme & Course interest
        'course_interested',
        'addon_course',
        'program_interested',
        'country',

        // Location
        'state',
        'district',

        // Application & payment tracking
        'application_number',
        'whatsapp_status',
        'application_form_status',
        'booking_status',
        'booking_payment',
        'fees_collection',
        'cancellation_reason',
        'cancelled_at',

        // Call & Follow-up
        'call_date',
        'call_status',
        'interest_level',
        'followup_date',
        'followup_status',
        'remarks',
        'next_action',
        'final_status',
        'admitted_at',
        'status',
    ];

    protected $casts = [
        'call_date'       => 'date',
        'followup_date'   => 'date',
        'admitted_at'     => 'datetime',
        'cancelled_at'    => 'datetime',
        'booking_payment' => 'decimal:2',
        'fees_collection' => 'decimal:2',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    // ── Boot ─────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (empty($lead->lead_code)) {
                $lead->lead_code = self::generateLeadCode();
            }
        });

        static::updated(function ($lead) {
            $changes = [];

            if ($lead->wasChanged('final_status')) {
                $changes['old_status'] = $lead->getOriginal('final_status');
                $changes['new_status'] = $lead->final_status;
            }

            if ($lead->wasChanged('interest_level')) {
                $changes['old_interest_level'] = $lead->getOriginal('interest_level');
                $changes['new_interest_level'] = $lead->interest_level;
            }

            if (!empty($changes)) {
                $lead->statusHistory()->create(array_merge($changes, [
                    'user_id' => auth()->id(),
                ]));
            }
        });

        // ── Cascade soft-delete to all related records ────────────────
        static::deleting(function ($lead) {
            $lead->followups()->each(fn($m) => $m->delete());
            $lead->callLogs()->each(fn($m) => $m->delete());
            $lead->notes()->each(fn($m) => $m->delete());
            $lead->statusHistory()->each(fn($m) => $m->delete());
        });

        // ── Cascade restore to all related records ────────────────────
        static::restoring(function ($lead) {
            $lead->followups()->withTrashed()->each(fn($m) => $m->restore());
            $lead->callLogs()->withTrashed()->each(fn($m) => $m->restore());
            $lead->notes()->withTrashed()->each(fn($m) => $m->restore());
            $lead->statusHistory()->withTrashed()->each(fn($m) => $m->restore());
        });
    }

    public static function generateLeadCode(): string
    {
        $year   = date('Y');
        $prefix = 'EDU';

        $lastLead = self::withTrashed()
            ->where('lead_code', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        $newNumber = $lastLead
            ? str_pad((int) substr($lastLead->lead_code, -4) + 1, 4, '0', STR_PAD_LEFT)
            : '0001';

        return "{$prefix}-{$year}-{$newNumber}";
    }

    // ── Relationships ─────────────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(EduLeadSource::class, 'lead_source_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Programme::class, 'program_id');
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(EduCallLog::class, 'edu_lead_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(EduLeadNote::class, 'edu_lead_id');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(EduLeadFollowup::class, 'edu_lead_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(EduLeadStatusHistory::class, 'edu_lead_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeHot($query)           { return $query->where('interest_level', 'hot'); }
    public function scopeWarm($query)          { return $query->where('interest_level', 'warm'); }
    public function scopeCold($query)          { return $query->where('interest_level', 'cold'); }
    public function scopePending($query)       { return $query->where('final_status', 'pending'); }
    public function scopeAdmitted($query)      { return $query->where('final_status', 'admitted'); }
    public function scopeNotInterested($query) { return $query->where('final_status', 'not_interested'); }
    public function scopeFromSchool($query)    { return $query->where('institution_type', 'school'); }
    public function scopeFromCollege($query)   { return $query->where('institution_type', 'college'); }

    /**
     * Scope visibility based on the authenticated user's role.
     * - telecaller   → only their own assigned leads
     * - lead_manager → all leads in their branch
     * - operation_head / super_admin → all leads
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isTelecaller()) {
            return $query->where('assigned_to', $user->id);
        }

        if ($user->isLeadManager()) {
            return $query->where('branch_id', $user->branch_id);
        }

        // super_admin and operation_head see everything
        return $query;
    }

    // ── Accessors ─────────────────────────────────────────────────────

    public function getInstitutionSummaryAttribute(): string
    {
        if ($this->institution_type === 'school') {
            $parts = array_filter([
                $this->school,
                $this->school_department ? $this->school_department . ' Dept.' : null,
            ]);
            return implode(' — ', $parts) ?: 'N/A';
        }

        if ($this->institution_type === 'college') {
            $parts = array_filter([
                $this->college,
                $this->college_department ? $this->college_department . ' Dept.' : null,
            ]);
            return implode(' — ', $parts) ?: 'N/A';
        }

        return 'N/A';
    }

    public function getProgramLabelAttribute(): string
    {
        return $this->program?->name ?? $this->program_interested ?? $this->course_interested ?? 'N/A';
    }

    public function getInterestLevelBadgeAttribute(): string
    {
        $badges = [
            'hot'  => '<span class="badge bg-danger">🔥 Hot</span>',
            'warm' => '<span class="badge bg-warning text-dark">☀️ Warm</span>',
            'cold' => '<span class="badge bg-info text-dark">❄️ Cold</span>',
        ];
        return $badges[$this->interest_level] ?? '<span class="badge bg-secondary">N/A</span>';
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending'             => '<span class="badge bg-warning text-dark">Pending</span>',
            'connected'           => '<span class="badge bg-info text-dark">Connected</span>',
            'not_connected'       => '<span class="badge bg-secondary">Not Connected</span>',
            'interested'          => '<span class="badge bg-success">Interested</span>',
            'not_interested'      => '<span class="badge bg-danger">Not Interested</span>',
            'follow_up_scheduled' => '<span class="badge bg-primary">Follow-up Scheduled</span>',
            'admitted'            => '<span class="badge bg-success">✅ Admitted</span>',
            'closed'              => '<span class="badge bg-dark">Closed</span>',
            'not_attended' => '<span class="badge bg-secondary">🚫 Not Attended</span>',
        ];
        return $badges[$this->status] ?? '<span class="badge bg-secondary">N/A</span>';
    }

    public function getInstitutionTypeBadgeAttribute(): string
    {
        return match($this->institution_type) {
            'school'  => '<span class="badge bg-primary">🏫 School</span>',
            'college' => '<span class="badge bg-success">🎓 College</span>',
            default   => '<span class="badge bg-secondary">N/A</span>',
        };
    }
}
