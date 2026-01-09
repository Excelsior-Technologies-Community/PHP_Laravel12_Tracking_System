<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use hisorange\BrowserDetect\Parser as Browser;

class ClickController extends Controller
{
    public function track($slug)
    {
        $trackingLink = TrackingLink::where('slug', $slug)->firstOrFail();
        
        // Track the click
        $this->recordClick($trackingLink);
        
        // Increment click count
        $trackingLink->incrementClickCount();
        
        // Redirect to original URL
        return redirect()->away($trackingLink->original_url);
    }

    private function recordClick($trackingLink)
    {
        $browser = new Browser();
        
        $clickData = [
            'tracking_link_id' => $trackingLink->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
            'device' => $browser->deviceType(),
            'browser' => $browser->browserName(),
            'platform' => $browser->platformName(),
        ];

        // Get location from IP (using free API)
        $location = $this->getLocationFromIp(request()->ip());
        if ($location) {
            $clickData['country'] = $location['country'] ?? null;
            $clickData['city'] = $location['city'] ?? null;
        }

        Click::create($clickData);
    }

    private function getLocationFromIp($ip)
    {
        try {
            // Using free IP geolocation API (ip-api.com)
            if ($ip !== '127.0.0.1') {
                $response = Http::get("http://ip-api.com/json/{$ip}");
                if ($response->successful()) {
                    return $response->json();
                }
            }
        } catch (\Exception $e) {
            // Log error or handle silently
        }
        
        return null;
    }
}