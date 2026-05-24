<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'identifier'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function practicums(): BelongsToMany
    {
        return $this->belongsToMany(
            Practicum::class,       // 1. Model Target
            'practicum_users',      // 2. Nama Tabel Pivot asli di DB
            'user_id',              // 3. Foreign key model ini di tabel pivot
            'practicum_id'          // 4. Foreign key model target di tabel pivot
        )->withPivot('role')->withTimestamps();
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    public function assignedSubmissions()
    {
        return $this->hasMany(Submission::class, 'assigned_assistant_id');
    }
}
