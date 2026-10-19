<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use hisorange\BrowserDetect\Parser as Browser;

class ClickController extends Controller
{
    /**
     * Track incoming click and redirect or prompt password.
     */
    public function track(Request $request, $slug)
    {
        $trackingLink = TrackingLink::where('slug', $slug)->firstOrFail();

        // 1. Check if link is inactive or expired
        if ($trackingLink->status === 'inactive' || $trackingLink->isExpired()) {
            return response()->view('tracking-links.expired', compact('trackingLink'), 410);
        }

        // 2. Check if password protected and not verified in current session
        if ($trackingLink->isPasswordProtected()) {
            $sessionKey = 'verified_link_' . $trackingLink->id;
            if (! session()->has($sessionKey)) {
                return view('tracking-links.password', compact('trackingLink'));
            }
        }

        return $this->processRedirection($request, $trackingLink);
    }

    /**
     * Verify password for protected link.
     */
    public function verifyPassword(Request $request, $slug)
    {
        $trackingLink = TrackingLink::where('slug', $slug)->firstOrFail();

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, $trackingLink->password)) {
            return back()->withErrors(['password' => 'Incorrect password. Access denied!']);
        }

        // Mark verified in session for 1 hour
        session()->put('verified_link_' . $trackingLink->id, true);

        return $this->processRedirection($request, $trackingLink);
    }

    /**
     * Process click registration and redirect to final destination (with UTM).
     */
    protected function processRedirection(Request $request, TrackingLink $trackingLink)
    {
        // Check bot & unique status
        $isBot = $this->detectBot($request);
        $isUnique = $this->isUniqueVisitor($request, $trackingLink);

        // Record click
        $this->recordClick($request, $trackingLink, $isBot, $isUnique);

        // Increment click count
        $trackingLink->incrementClickCount();

        // Auto-expire if click limit reached
        if ($trackingLink->click_limit && $trackingLink->click_count >= $trackingLink->click_limit) {
            $trackingLink->update(['status' => 'expired']);
        }

        return redirect()->away($trackingLink->final_destination_url);
    }

    /**
     * Record click analytics.
     */
    private function recordClick(Request $request, TrackingLink $trackingLink, bool $isBot, bool $isUnique): void
    {
        $browser = new Browser();

        $clickData = [
            'tracking_link_id' => $trackingLink->id,
            'ip_address'       => $request->ip(),
            'user_agent'       => $request->userAgent(),
            'referrer'         => $request->header('referer'),
            'device'           => $isBot ? 'Bot/Crawler' : ($browser->deviceType() ?: 'Desktop'),
            'browser'          => $isBot ? 'Search Bot' : ($browser->browserName() ?: 'Unknown'),
            'platform'         => $browser->platformName() ?: 'Unknown',
            'is_bot'           => $isBot,
            'is_unique'        => $isUnique,
        ];

        // Geolocation
        $location = $this->getLocationFromIp($request->ip());
        if ($location) {
            $clickData['country'] = $location['country'] ?? null;
            $clickData['city']    = $location['city'] ?? null;
        }

        Click::create($clickData);
    }

    /**
     * Detect if request comes from bot/crawler.
     */
    protected function detectBot(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        if (empty($userAgent)) {
            return true;
        }

        $botSignatures = [
            'googlebot', 'bingbot', 'yandex', 'baiduspider', 'facebookexternalhit',
            'twitterbot', 'rogerbot', 'linkedinbot', 'embedly', 'quora link preview',
            'showyoubot', 'outbrain', 'pinterest/0.', 'developers.google.com/+/web/snippet',
            'slackbot', 'vkshare', 'w3c_validator', 'redditbot', 'applebot',
            'whatsapp', 'flipboard', 'tumblr', 'bitlybot', 'skypeuripreview',
            'nuzzel', 'discordbot', 'google page speed', 'qwantify', 'cortex',
            'headlesschrome', 'curl', 'wget', 'python', 'postman'
        ];

        foreach ($botSignatures as $bot) {
            if (str_contains($userAgent, $bot)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if this IP has clicked this link before.
     */
    protected function isUniqueVisitor(Request $request, TrackingLink $trackingLink): bool
    {
        $ip = $request->ip();

        return ! Click::where('tracking_link_id', $trackingLink->id)
            ->where('ip_address', $ip)
            ->exists();
    }

    /**
     * Geolocation lookup with graceful timeout.
     */
    private function getLocationFromIp($ip)
    {
        try {
            if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.')) {
                $ip = '8.8.8.8'; // fallback for localhost testing
            }

            $response = Http::timeout(3)->get("https://ipwho.is/{$ip}");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success']) {
                    return [
                        'country' => $data['country'] ?? null,
                        'city'    => $data['city'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('IP Location lookup failed: ' . $e->getMessage());
        }

        return null;
    }
}
