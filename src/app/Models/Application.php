<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'breaking_id',
        'new_date',
        'new_start_time',
        'new_end_time',
        'new_break_in',
        'new_break_out',
        'comment',
        'approval',
        'requested_date',
    ];

    const APPROVAL_PENDING = 0;
    const APPROVAL_APPROVED = 1;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breaking()
    {
        return $this->belongsTo(Breaking::class);
    }

    public function getApprovalLabelAttribute()
    {
        switch ($this->approval) {
            case self::APPROVAL_APPROVED:
                return '承認';
            default:
                return '未承認';
        }
    }
}
