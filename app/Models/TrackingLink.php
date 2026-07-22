<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingLink extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [

        'name',
        'original_url',
        'slug',
        'click_count',
        'status'

    ];


    protected $dates = [
        'deleted_at'
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



    // Restore link
    public function restoreLink()
    {
        $this->restore();

        $this->update([
            'status' => 'active'
        ]);
    }



    // Soft delete link
    public function deleteLink()
    {
        $this->update([
            'status' => 'deleted'
        ]);

        $this->delete();
    }
}
