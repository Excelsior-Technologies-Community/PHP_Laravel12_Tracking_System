<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class TrackingLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'original_url',
        'slug',
        'click_count',
        'status',
        'password',
        'expires_at',
        'click_limit',
        'qr_color',
        'qr_bg_color',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'deleted_at' => 'datetime',
        'click_count' => 'integer',
        'click_limit' => 'integer',
    ];

    public function clicks()
    {
        return $this->hasMany(Click::class);
    }

    public function realClicks()
    {
        return $this->hasMany(Click::class)->where('is_bot', false);
    }

    public function uniqueClicks()
    {
        return $this->hasMany(Click::class)->where('is_bot', false)->where('is_unique', true);
    }

    public function botClicks()
    {
        return $this->hasMany(Click::class)->where('is_bot', true);
    }

    public function getTrackingUrlAttribute(): string
    {
        return route('tracking.click', $this->slug);
    }

    public function getFinalDestinationUrlAttribute(): string
    {
        $url = $this->original_url;

        $params = [];
        if (! empty($this->utm_source))   $params['utm_source']   = $this->utm_source;
        if (! empty($this->utm_medium))   $params['utm_medium']   = $this->utm_medium;
        if (! empty($this->utm_campaign)) $params['utm_campaign'] = $this->utm_campaign;
        if (! empty($this->utm_term))     $params['utm_term']     = $this->utm_term;

        if (! empty($params)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($params);
        }

        return $url;
    }

    public function isExpired(): bool
    {
        if ($this->expires_at && Carbon::now()->greaterThanOrEqualTo($this->expires_at)) {
            return true;
        }

        if ($this->click_limit && $this->click_count >= $this->click_limit) {
            return true;
        }

        return false;
    }

    public function isPasswordProtected(): bool
    {
        return ! empty($this->password);
    }

    public function incrementClickCount(): void
    {
        $this->increment('click_count');
    }

    public function restoreLink(): void
    {
        $this->restore();

        $this->update([
            'status' => 'active'
        ]);
    }

    public function deleteLink(): void
    {
        $this->update([
            'status' => 'deleted'
        ]);

        $this->delete();
    }
}
