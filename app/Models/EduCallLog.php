<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EduCallLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'edu_lead_id',
        'user_id',
        'call_datetime',
        'call_status',
        'interest_level',
        'remarks',
        'next_action',
        'followup_date',
    ];

    protected $casts = [
        'call_datetime' => 'datetime',
        'followup_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
