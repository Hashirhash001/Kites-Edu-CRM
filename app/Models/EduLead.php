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
        'lead_source_id',
        'course_id',
        'name',
        'email',
        'phone',
        'whatsapp_number',
        'description',
        'course_interested',
        'country',
        'college',
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
        'call_date' => 'date',
        'followup_date' => 'date',
        'admitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Auto-generate lead code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (empty($lead->lead_code)) {
                $lead->lead_code = self::generateLeadCode();
            }
        });
    }

    /**
     * Generate unique lead code: EDU-2026-0001
     */
    public static function generateLeadCode(): string
    {
        $year = date('Y');
        $prefix = 'EDU';

        $lastLead = self::withTrashed()
            ->where('lead_code', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastLead) {
            $lastNumber = (int) substr($lastLead->lead_code, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$year}-{$newNumber}";
    }

    // Relationships
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

    // Scopes
    public function scopeHot($query)
    {
        return $query->where('interest_level', 'hot');
    }

    public function scopeWarm($query)
    {
        return $query->where('interest_level', 'warm');
    }

    public function scopeCold($query)
    {
        return $query->where('interest_level', 'cold');
    }

    public function scopePending($query)
    {
        return $query->where('final_status', 'pending');
    }

    public function scopeAdmitted($query)
    {
        return $query->where('final_status', 'admitted');
    }

    public function scopeNotInterested($query)
    {
        return $query->where('final_status', 'not_interested');
    }

    // Accessors
    public function getInterestLevelBadgeAttribute()
    {
        $badges = [
            'hot' => '<span class="badge bg-danger">🔥 Hot</span>',
            'warm' => '<span class="badge bg-warning">☀️ Warm</span>',
            'cold' => '<span class="badge bg-info">❄️ Cold</span>',
        ];

        return $badges[$this->interest_level] ?? '<span class="badge bg-secondary">N/A</span>';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'connected' => '<span class="badge bg-info">Connected</span>',
            'not_connected' => '<span class="badge bg-secondary">Not Connected</span>',
            'interested' => '<span class="badge bg-success">Interested</span>',
            'not_interested' => '<span class="badge bg-danger">Not Interested</span>',
            'follow_up_scheduled' => '<span class="badge bg-primary">Follow-up Scheduled</span>',
            'admitted' => '<span class="badge bg-success">✅ Admitted</span>',
            'closed' => '<span class="badge bg-dark">Closed</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">N/A</span>';
    }
}
