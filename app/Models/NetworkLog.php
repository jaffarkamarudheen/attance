<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkLog extends Model
{
    protected $fillable = [
        'user_id',
        'ssid',
        'local_ip',
        'public_ip',
        'device_name',
        'latitude',
        'longitude',
        'location_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
