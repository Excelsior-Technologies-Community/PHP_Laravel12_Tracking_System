<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class TrackingLinkController extends Controller
{

    /**
     * Display all tracking links
     * Features:
     * - Search
     * - Status Filter
     * - Pagination
     * - Statistics
     */
    public function index(Request $request)
    {

        $query = TrackingLink::withCount('clicks');

        if ($request->search) {

            $search = $request->search;


            $query->where(function ($q) use ($search) {

                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('original_url', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }


        if ($request->status) {

            $query->where(
                'status',
                $request->status
            );
        }

        $links = $query
            ->oldest()
            ->paginate(4)
            ->withQueryString();


        $stats = [

            'total' => TrackingLink::count(),

            'active' => TrackingLink::where(
                'status',
                'active'
            )->count(),


            'deleted' => TrackingLink::onlyTrashed()
                ->count(),


            'clicks' => TrackingLink::sum(
                'click_count'
            )

        ];


        return view(
            'tracking-links.index',
            compact(
                'links',
                'stats'
            )
        );
    }





    /**
     * Create Page
     */
    public function create()
    {
        return view(
            'tracking-links.create'
        );
    }



    /**
     * Check if a custom slug is available (for live AJAX validation)
     */
    public function checkSlug(Request $request)
    {
        $slug = Str::slug($request->input('slug', ''));
        if (empty($slug)) {
            return response()->json(['available' => false, 'message' => 'Slug cannot be empty.']);
        }

        $exists = TrackingLink::withTrashed()->where('slug', $slug)->exists();

        return response()->json([
            'available' => ! $exists,
            'slug'      => $slug,
            'message'   => $exists ? 'This custom slug is already taken!' : 'Slug is available!',
        ]);
    }

    /**
     * Store Tracking Link
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'original_url' => 'required|url|max:500',
            'custom_slug'  => 'nullable|string|alpha_dash|max:50|unique:tracking_links,slug',
            'password'     => 'nullable|string|min:4|max:50',
            'expires_at'   => 'nullable|date|after:now',
            'click_limit'  => 'nullable|integer|min:1',
            'qr_color'     => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'qr_bg_color'  => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'utm_source'   => 'nullable|string|max:100',
            'utm_medium'   => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'utm_term'     => 'nullable|string|max:100',
        ]);

        // Slug generation
        if (! empty($validated['custom_slug'])) {
            $validated['slug'] = Str::slug($validated['custom_slug']);
        } else {
            $validated['slug'] = Str::random(8);
        }
        unset($validated['custom_slug']);

        // Password hashing
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            $validated['password'] = null;
        }

        $validated['click_count'] = 0;
        $validated['status']      = 'active';
        $validated['qr_color']    = $validated['qr_color'] ?? '#000000';
        $validated['qr_bg_color'] = $validated['qr_bg_color'] ?? '#ffffff';

        TrackingLink::create($validated);

        return redirect()
            ->route('tracking-links.index')
            ->with('success', 'Tracking link created successfully!');
    }

    /**
     * Show Details with Charts and Advanced Analytics
     */
    public function show(TrackingLink $trackingLink)
    {
        $clicks = $trackingLink
            ->clicks()
            ->latest()
            ->paginate(10);

        $allClicks = $trackingLink->clicks;

        // 1. Total, Unique, and Bot counts
        $totalClicksCount  = $allClicks->count();
        $uniqueClicksCount = $allClicks->where('is_bot', false)->where('is_unique', true)->count();
        $botClicksCount    = $allClicks->where('is_bot', true)->count();
        $humanClicksCount  = $allClicks->where('is_bot', false)->count();

        // 2. Timeline Chart (Last 7 Days)
        $dates = [];
        $humanTrend = [];
        $botTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::now()->subDays($i)->format('d M');

            $humanTrend[] = $allClicks->filter(function ($c) use ($date) {
                return Carbon::parse($c->created_at)->format('Y-m-d') === $date && ! $c->is_bot;
            })->count();

            $botTrend[] = $allClicks->filter(function ($c) use ($date) {
                return Carbon::parse($c->created_at)->format('Y-m-d') === $date && $c->is_bot;
            })->count();
        }

        // 3. Devices breakdown (Human only)
        $devices = $allClicks->where('is_bot', false)
            ->groupBy(fn($c) => $c->device ?: 'Unknown')
            ->map->count();

        // 4. Browsers breakdown (Human only)
        $browsers = $allClicks->where('is_bot', false)
            ->groupBy(fn($c) => $c->browser ?: 'Unknown')
            ->map->count();

        // 5. Top Countries breakdown
        $countries = $allClicks->where('is_bot', false)
            ->whereNotNull('country')
            ->groupBy('country')
            ->map->count()
            ->sortDesc()
            ->take(5);

        // 6. Top Referrers breakdown
        $referrers = $allClicks->where('is_bot', false)
            ->groupBy(function ($c) {
                if (empty($c->referrer)) return 'Direct / None';
                $host = parse_url($c->referrer, PHP_URL_HOST);
                return $host ?: 'Direct / None';
            })
            ->map->count()
            ->sortDesc()
            ->take(5);

        // Hex to RGB helper for QR generator
        $hexToRgb = function ($hex) {
            $hex = ltrim($hex, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ];
        };

        $qrColorRgb = $hexToRgb($trackingLink->qr_color ?: '#000000');
        $qrBgRgb    = $hexToRgb($trackingLink->qr_bg_color ?: '#ffffff');

        $chartData = [
            'labels'      => $dates,
            'humanTrend'  => $humanTrend,
            'botTrend'    => $botTrend,
            'deviceLabels'=> $devices->keys()->toArray(),
            'deviceValues'=> $devices->values()->toArray(),
            'browserLabels'=> $browsers->keys()->toArray(),
            'browserValues'=> $browsers->values()->toArray(),
            'countryLabels'=> $countries->keys()->toArray(),
            'countryValues'=> $countries->values()->toArray(),
            'refLabels'   => $referrers->keys()->toArray(),
            'refValues'   => $referrers->values()->toArray(),
        ];

        return view(
            'tracking-links.show',
            compact(
                'trackingLink',
                'clicks',
                'totalClicksCount',
                'uniqueClicksCount',
                'botClicksCount',
                'humanClicksCount',
                'chartData',
                'qrColorRgb',
                'qrBgRgb'
            )
        );
    }

    public function downloadQr(Request $request, TrackingLink $trackingLink)
    {
        $hexToRgb = function ($hex) {
            $hex = ltrim($hex, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ];
        };

        $color = $hexToRgb($trackingLink->qr_color ?: '#000000');
        $bgColor = $hexToRgb($trackingLink->qr_bg_color ?: '#ffffff');

        $qrCode = QrCode::format('svg')
            ->size(350)
            ->color($color[0], $color[1], $color[2])
            ->backgroundColor($bgColor[0], $bgColor[1], $bgColor[2])
            ->margin(2)
            ->generate($trackingLink->tracking_url);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header(
                'Content-Disposition',
                'attachment; filename="qr-' . $trackingLink->slug . '.svg"'
            );
    }

    /**
     * Soft Delete
     */
    public function destroy(TrackingLink $trackingLink)
    {

        $trackingLink->deleteLink();



        return redirect()
            ->route('tracking-links.index')
            ->with(
                'success',
                'Tracking link moved to trash!'
            );
    }

    /**
     * Trash Page
     */
    public function trash()
    {

        $links = TrackingLink::onlyTrashed()
            ->latest()
            ->paginate(10);



        return view(
            'tracking-links.trash',
            compact('links')
        );
    }


    /**
     * Restore Deleted Link
     */
    public function restore($id)
    {

        $link = TrackingLink::onlyTrashed()
            ->findOrFail($id);



        $link->restoreLink();



        return redirect()
            ->route('tracking-links.trash')
            ->with(
                'success',
                'Tracking link restored successfully!'
            );
    }


    /**
     * Permanent Delete
     */
    public function forceDelete($id)
    {

        $link = TrackingLink::onlyTrashed()
            ->findOrFail($id);



        $link->forceDelete();



        return redirect()
            ->route('tracking-links.trash')
            ->with(
                'success',
                'Tracking link permanently deleted!'
            );
    }

    /**
     * Export Click History as CSV
     */
    public function exportCsv(Request $request, TrackingLink $trackingLink): StreamedResponse
    {
        $filter = $request->get('filter', 'all');

        $clicks = $trackingLink->clicks()->latest();

        switch ($filter) {

            case 'today':
                $clicks->whereDate('created_at', Carbon::today());
                break;

            case '7days':
                $clicks->where('created_at', '>=', Carbon::now()->subDays(7));
                break;

            case '30days':
                $clicks->where('created_at', '>=', Carbon::now()->subDays(30));
                break;

            default:
                // Export all clicks
                break;
        }

        $filename = 'tracking_' .
            $trackingLink->slug .
            '_' .
            $filter .
            '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->stream(function () use ($clicks) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date',
                'IP Address',
                'Country',
                'City',
                'Browser',
                'Device',
                'Platform',
                'Referrer'
            ]);

            foreach ($clicks->get() as $click) {

                fputcsv($handle, [

                    $click->created_at,

                    $click->ip_address,

                    $click->country,

                    $click->city,

                    $click->browser,

                    $click->device,

                    $click->platform,

                    $click->referrer

                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
 * Export Tracking Links CSV
 */
public function exportTrackingLinks(Request $request): StreamedResponse
{

    $query = TrackingLink::withCount('clicks');

    if ($request->search) {

        $search = $request->search;

        $query->where(function ($q) use ($search) {

            $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('original_url', 'LIKE', "%{$search}%")
                ->orWhere('slug', 'LIKE', "%{$search}%");

        });

    }

    if ($request->status) {

        $query->where(
            'status',
            $request->status
        );

    }

    $links = $query
        ->oldest()
        ->get();

    $headers = [

        'Content-Type' => 'text/csv',

        'Content-Disposition' => 'attachment; filename=tracking_links.csv',

    ];

    return response()->stream(function () use ($links) {

        $handle = fopen('php://output', 'w');

        fputcsv($handle, [

            'Name',

            'Original URL',

            'Tracking URL',

            'Clicks',

            'Status',

            'Created Date',

        ]);

        foreach ($links as $link) {

            fputcsv($handle, [

                $link->name,

                $link->original_url,

                $link->tracking_url,

                $link->clicks_count,

                ucfirst($link->status),

                $link->created_at->format('d M Y H:i'),

            ]);

        }

        fclose($handle);

    }, 200, $headers);

}
}
