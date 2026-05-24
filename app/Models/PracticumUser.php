<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticumUser extends Model
{
    protected $fillable = ['practicum_id', 'user_id', 'role'];

    public function practicums()
    {
        return $this->belongsTo(Practicum::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
