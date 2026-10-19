<?php

namespace Database\Seeders;

use App\Models\TrackingLink;
use App\Models\Click;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed realistic tracking links and analytics clicks.
     */
    public function run(): void
    {
        // 1. Promotional Campaign Link (with Custom Slug, UTM, and Custom QR Colors)
        $link1 = TrackingLink::create([
            'name'         => 'Diwali Mega Sale 2026',
            'original_url' => 'https://example.com/diwali-offers',
            'slug'         => 'diwali-sale-2026',
            'status'       => 'active',
            'click_count'  => 0,
            'qr_color'     => '#b02a37', // Festive dark red
            'qr_bg_color'  => '#fff3cd', // Festive golden light
            'utm_source'   => 'instagram',
            'utm_medium'   => 'bio_link',
            'utm_campaign' => 'diwali_festive_promo',
            'utm_term'     => 'ethnic_wear',
            'expires_at'   => Carbon::now()->addDays(15),
            'click_limit'  => 500,
        ]);

        // 2. Secret Protected Link (Password Protected)
        $link2 = TrackingLink::create([
            'name'         => 'Confidential Investor Pitch Deck',
            'original_url' => 'https://example.com/investor-pitch-deck-2026.pdf',
            'slug'         => 'pitch-deck-secret',
            'status'       => 'active',
            'click_count'  => 0,
            'password'     => Hash::make('secret123'),
            'qr_color'     => '#0d6efd',
            'qr_bg_color'  => '#ffffff',
            'expires_at'   => Carbon::now()->addDays(7),
        ]);

        // 3. Expired Limited Flash Offer (Expired by date & clicks)
        $link3 = TrackingLink::create([
            'name'         => 'Midnight Flash Clearance 90% Off',
            'original_url' => 'https://example.com/clearance-sale',
            'slug'         => 'midnight-flash-90',
            'status'       => 'expired',
            'click_count'  => 50,
            'click_limit'  => 50,
            'expires_at'   => Carbon::now()->subHours(5),
            'qr_color'     => '#000000',
            'qr_bg_color'  => '#ffffff',
        ]);

        // Seed 40+ diverse clicks for Link 1 across the last 7 days
        $sampleData = [
            ['ip' => '103.21.124.1', 'country' => 'India', 'city' => 'Ahmedabad', 'device' => 'Mobile', 'browser' => 'Chrome', 'platform' => 'Android', 'ref' => 'https://instagram.com/'],
            ['ip' => '103.21.124.1', 'country' => 'India', 'city' => 'Ahmedabad', 'device' => 'Mobile', 'browser' => 'Chrome', 'platform' => 'Android', 'ref' => 'https://instagram.com/'], // repeated IP -> not unique
            ['ip' => '49.36.180.22', 'country' => 'India', 'city' => 'Mumbai', 'device' => 'Mobile', 'browser' => 'Safari', 'platform' => 'iOS', 'ref' => 'https://instagram.com/'],
            ['ip' => '122.161.45.10', 'country' => 'India', 'city' => 'Delhi', 'device' => 'Desktop', 'browser' => 'Chrome', 'platform' => 'Windows', 'ref' => 'https://google.com/'],
            ['ip' => '103.246.40.18', 'country' => 'India', 'city' => 'Surat', 'device' => 'Mobile', 'browser' => 'Chrome', 'platform' => 'Android', 'ref' => 'https://l.instagram.com/'],
            ['ip' => '66.249.66.1', 'country' => 'United States', 'city' => 'Mountain View', 'device' => 'Bot/Crawler', 'browser' => 'Search Bot', 'platform' => 'Unknown', 'ref' => null, 'bot' => true],
            ['ip' => '157.55.39.20', 'country' => 'United States', 'city' => 'Redmond', 'device' => 'Bot/Crawler', 'browser' => 'Search Bot', 'platform' => 'Unknown', 'ref' => null, 'bot' => true],
            ['ip' => '20.198.118.5', 'country' => 'United Kingdom', 'city' => 'London', 'device' => 'Desktop', 'browser' => 'Firefox', 'platform' => 'Mac OS', 'ref' => 'https://t.co/'],
            ['ip' => '185.199.110.153', 'country' => 'Canada', 'city' => 'Toronto', 'device' => 'Desktop', 'browser' => 'Edge', 'platform' => 'Windows', 'ref' => 'https://linkedin.com/'],
            ['ip' => '117.200.55.90', 'country' => 'India', 'city' => 'Bangalore', 'device' => 'Tablet', 'browser' => 'Safari', 'platform' => 'iPadOS', 'ref' => 'https://web.whatsapp.com/'],
        ];

        $visitedIps = [];

        for ($day = 6; $day >= 0; $day--) {
            $clicksPerDay = random_int(4, 8);
            for ($k = 0; $k < $clicksPerDay; $k++) {
                $sample = $sampleData[array_rand($sampleData)];
                $isBot = ! empty($sample['bot']);
                $ip = $sample['ip'];

                $isUnique = false;
                if (! in_array($ip, $visitedIps)) {
                    $visitedIps[] = $ip;
                    $isUnique = true;
                }

                $createdAt = Carbon::now()->subDays($day)->subHours(random_int(1, 20))->subMinutes(random_int(1, 55));

                Click::create([
                    'tracking_link_id' => $link1->id,
                    'ip_address'       => $ip,
                    'user_agent'       => 'Mozilla/5.0 (Sample UA)',
                    'referrer'         => $sample['ref'],
                    'country'          => $sample['country'],
                    'city'             => $sample['city'],
                    'device'           => $sample['device'],
                    'browser'          => $sample['browser'],
                    'platform'         => $sample['platform'],
                    'is_bot'           => $isBot,
                    'is_unique'        => $isUnique,
                    'created_at'       => $createdAt,
                    'updated_at'       => $createdAt,
                ]);

                $link1->incrementClickCount();
            }
        }
    }
}
