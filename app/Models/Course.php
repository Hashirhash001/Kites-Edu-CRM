<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'programme_id', 'name', 'duration', 'description', 'country', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id');
    }

    public function eduLeads()
    {
        return $this->hasMany(EduLead::class, 'course_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
