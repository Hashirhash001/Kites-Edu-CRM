<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    const ROLES = [
        'super_admin'    => 'Super Admin',
        'operation_head' => 'Operation Head',
        'lead_manager'   => 'Lead Manager',
        'telecaller'     => 'Telecaller',       // ← NEW
    ];

    // Roles that don't require a branch
    const BRANCH_FREE_ROLES = ['super_admin', 'operation_head'];

    protected $fillable = [
        'name', 'email', 'password',
        'phone', 'role', 'branch_id', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdEduLeads()
    {
        return $this->hasMany(EduLead::class, 'created_by');
    }

    public function assignedEduLeads()
    {
        return $this->hasMany(EduLead::class, 'assigned_to');
    }

    public function eduCallLogs()
    {
        return $this->hasMany(EduCallLog::class, 'user_id');
    }

    public function eduLeadNotes()
    {
        return $this->hasMany(EduLeadNote::class, 'created_by');
    }

    public function assignedEduFollowups()
    {
        return $this->hasMany(EduLeadFollowup::class, 'assigned_to');
    }

    public function createdEduFollowups()
    {
        return $this->hasMany(EduLeadFollowup::class, 'created_by');
    }

    public function eduLeadStatusChanges()
    {
        return $this->hasMany(EduLeadStatusHistory::class, 'user_id');
    }

    public function eduLeadImports()
    {
        return $this->hasMany(EduLeadImport::class, 'user_id');
    }

    // ── Role Helpers ──────────────────────────────────────────────────

    public function isSuperAdmin():    bool { return $this->role === 'super_admin'; }
    public function isOperationHead(): bool { return $this->role === 'operation_head'; }
    public function isLeadManager():   bool { return $this->role === 'lead_manager'; }
    public function isTelecaller():    bool { return $this->role === 'telecaller'; }  // ← NEW
    public function isActive():        bool { return $this->is_active; }

    public function requiresBranch(): bool
    {
        return !in_array($this->role, self::BRANCH_FREE_ROLES);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function canDelete(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Can this user assign leads?
     * super_admin, operation_head, lead_manager only
     */
    public function canAssignLeads(): bool
    {
        return in_array($this->role, ['super_admin', 'operation_head', 'lead_manager']);
    }

    /**
     * Can this user create leads?
     * All roles except none — telecallers can create too
     */
    public function canCreateLeads(): bool
    {
        return in_array($this->role, ['super_admin', 'operation_head', 'lead_manager', 'telecaller']);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeActive($query)        { return $query->where('is_active', true); }
    public function scopeLeadManagers($query)  { return $query->where('role', 'lead_manager'); }
    public function scopeTelecallers($query)   { return $query->where('role', 'telecaller'); }  // ← NEW
    public function scopeInBranch($query, $id) { return $query->where('branch_id', $id); }
}
