<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionVersion extends Model
{
    protected $fillable = [
        'submission_id',
        'version_number',
        'word_file_path',
        'pdf_file_path',
        'attachment',
        'is_format_valid',
        'system_validation_logs',
        'assistant_notes',
        'annotation_coordinates'
    ];

    protected $casts = [
        'attachment'             => 'array', // Cast otomatis JSON ke Array PHP
        'system_validation_logs' => 'array', // Cast otomatis JSON ke Array PHP
        'annotation_coordinates' => 'array', // Cast otomatis JSON ke Array PHP
        'is_format_valid'        => 'boolean',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
