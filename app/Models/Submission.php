<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'assigned_assistant_id',
        'status', // 'pending', 'revision', 'approved'
        'final_score',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assistant()
    {
        return $this->belongsTo(User::class, 'assigned_assistant_id');
    }

    public function versions()
    {
        return $this->hasMany(SubmissionVersion::class)->latest();
    }

    public function latestVersion()
    {
        return $this->hasOne(SubmissionVersion::class)->latestOfMany();
    }
}
