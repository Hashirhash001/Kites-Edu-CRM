<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'branch_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    // ---------- EDUCATION CRM RELATIONSHIPS ----------

    /**
     * Get education leads created by this user
     */
    public function createdEduLeads()
    {
        return $this->hasMany(EduLead::class, 'created_by');
    }

    /**
     * Get education leads assigned to this user (for telecallers)
     */
    public function assignedEduLeads()
    {
        return $this->hasMany(EduLead::class, 'assigned_to');
    }

    /**
     * Get call logs made by this user
     */
    public function eduCallLogs()
    {
        return $this->hasMany(EduCallLog::class, 'user_id');
    }

    /**
     * Get notes created by this user
     */
    public function eduLeadNotes()
    {
        return $this->hasMany(EduLeadNote::class, 'created_by');
    }

    /**
     * Get followups assigned to this user
     */
    public function assignedEduFollowups()
    {
        return $this->hasMany(EduLeadFollowup::class, 'assigned_to');
    }

    /**
     * Get followups created by this user
     */
    public function createdEduFollowups()
    {
        return $this->hasMany(EduLeadFollowup::class, 'created_by');
    }

    /**
     * Get status history changes made by this user
     */
    public function eduLeadStatusChanges()
    {
        return $this->hasMany(EduLeadStatusHistory::class, 'user_id');
    }

    /**
     * Get import records by this user
     */
    public function eduLeadImports()
    {
        return $this->hasMany(EduLeadImport::class, 'user_id');
    }

    // ==================== ROLE HELPER METHODS ====================

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isLeadManager()
    {
        return $this->role === 'lead_manager';
    }

    public function isFieldStaff()
    {
        return $this->role === 'field_staff';
    }

    public function isTelecaller()
    {
        return $this->role === 'telecallers';
    }

    public function isReportingUser()
    {
        return $this->role === 'reporting_user';
    }

    // Status helper
    public function isActive()
    {
        return $this->is_active;
    }

    // ==================== EDUCATION CRM PERFORMANCE METRICS ====================

    /**
     * Get total calls made today
     */
    public function getTodayCallsCountAttribute()
    {
        return $this->eduCallLogs()
            ->whereDate('call_datetime', today())
            ->count();
    }

    /**
     * Get connected calls made today
     */
    public function getConnectedCallsTodayAttribute()
    {
        return $this->eduCallLogs()
            ->whereDate('call_datetime', today())
            ->where('call_status', 'connected')
            ->count();
    }

    /**
     * Get hot leads assigned today
     */
    public function getHotLeadsTodayAttribute()
    {
        return $this->assignedEduLeads()
            ->where('interest_level', 'hot')
            ->whereDate('updated_at', today())
            ->count();
    }

    /**
     * Get total admissions closed by this user
     */
    public function getAdmissionsClosedAttribute()
    {
        return $this->assignedEduLeads()
            ->where('final_status', 'admitted')
            ->count();
    }

    /**
     * Get pending followups for today
     */
    public function getPendingFollowupsTodayAttribute()
    {
        return $this->assignedEduFollowups()
            ->whereDate('followup_date', today())
            ->where('status', 'pending')
            ->count();
    }

    /**
     * Get overdue followups
     */
    public function getOverdueFollowupsAttribute()
    {
        return $this->assignedEduFollowups()
            ->where('followup_date', '<', today())
            ->where('status', 'pending')
            ->count();
    }

    /**
     * Get total hot leads assigned to this user
     */
    public function getTotalHotLeadsAttribute()
    {
        return $this->assignedEduLeads()
            ->where('interest_level', 'hot')
            ->where('final_status', 'pending')
            ->count();
    }

    /**
     * Get conversion rate (admitted / total assigned)
     */
    public function getConversionRateAttribute()
    {
        $totalAssigned = $this->assignedEduLeads()->count();
        if ($totalAssigned == 0) return 0;

        $admitted = $this->assignedEduLeads()->where('final_status', 'admitted')->count();
        return round(($admitted / $totalAssigned) * 100, 2);
    }

    // ==================== SCOPES ====================

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for telecallers
     */
    public function scopeTelecallers($query)
    {
        return $query->where('role', 'telecallers');
    }

    /**
     * Scope for lead managers
     */
    public function scopeLeadManagers($query)
    {
        return $query->where('role', 'lead_manager');
    }

    /**
     * Scope for users in a specific branch
     */
    public function scopeInBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
