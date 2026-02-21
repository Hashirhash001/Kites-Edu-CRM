<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function eduLeads()
    {
        return $this->hasMany(EduLead::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
