<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EduLeadStatusHistory extends Model
{
    use SoftDeletes;

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
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eduLead(): BelongsTo
    {
        return $this->belongsTo(EduLead::class);
    }
}
