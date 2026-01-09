<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_link_id',
        'ip_address',
        'user_agent',
        'referrer',
        'country',
        'city',
        'device',
        'browser',
        'platform'
    ];

    public function trackingLink()
    {
        return $this->belongsTo(TrackingLink::class);
    }
}