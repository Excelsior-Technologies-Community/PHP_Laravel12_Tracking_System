<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_url',
        'slug',
        'click_count'
    ];

    public function clicks()
    {
        return $this->hasMany(Click::class);
    }

    public function getTrackingUrlAttribute()
    {
        return route('tracking.click', $this->slug);
    }

    public function incrementClickCount()
    {
        $this->increment('click_count');
    }
}