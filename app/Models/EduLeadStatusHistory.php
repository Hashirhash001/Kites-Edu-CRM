<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EduLeadStatusHistory extends Model
{
    protected $fillable = [
        'edu_lead_id',
        'user_id',
        'old_status',
        'new_status',
        'old_interest_level',
        'new_interest_level',
        'remarks',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function eduLead(): BelongsTo
    {
        return $this->belongsTo(EduLead::class, 'edu_lead_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
