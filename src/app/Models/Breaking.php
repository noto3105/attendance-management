<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Breaking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'date',
        'break_in',
        'break_out',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // Breaking.php
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

}
