<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'practicum_id',
        'title',
        'description',
        'type', // 'module' atau 'task'
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function practicum()
    {
        return $this->belongsTo(Practicum::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
