<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EduLeadImport extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'status',
        'errors',
        'failed_rows_data',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'successful_rows' => 'integer',
        'failed_rows' => 'integer',
        'errors' => 'array',
        'failed_rows_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
