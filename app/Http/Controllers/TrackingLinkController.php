<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
}
