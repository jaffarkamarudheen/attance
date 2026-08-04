<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'user_id',
        'punch_in',
        'punch_out',
    ];

    protected $casts = [
        'punch_in' => 'datetime',
        'punch_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
