<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function courses()
    {
        return $this->hasMany(Course::class, 'programme_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
