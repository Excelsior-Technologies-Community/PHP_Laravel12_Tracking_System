<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
     * Store Tracking Link
     */
    public function store(Request $request)
    {

        $validated = $request->validate([

            'name' => 'required|string|max:255',

            'original_url' => 'required|url|max:500',

        ]);



        $validated['slug'] = Str::random(8);


        $validated['click_count'] = 0;


        $validated['status'] = 'active';



        TrackingLink::create($validated);



        return redirect()
            ->route('tracking-links.index')
            ->with(
                'success',
                'Tracking link created successfully!'
            );
    }


    /**
     * Show Details
     */
    public function show(TrackingLink $trackingLink)
    {

        $clicks = $trackingLink
            ->clicks()
            ->latest()
            ->paginate(10);



        return view(
            'tracking-links.show',
            compact(
                'trackingLink',
                'clicks'
            )
        );
    }

    public function downloadQr(TrackingLink $trackingLink)
    {
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($trackingLink->tracking_url);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header(
                'Content-Disposition',
                'attachment; filename="tracking-link-' . $trackingLink->id . '.svg"'
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
