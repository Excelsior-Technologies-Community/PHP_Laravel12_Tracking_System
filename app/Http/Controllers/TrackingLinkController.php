<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackingLinkController extends Controller
{
    public function index()
    {
        $links = TrackingLink::withCount('clicks')->latest()->get();
        return view('tracking-links.index', compact('links'));
    }

    public function create()
    {
        return view('tracking-links.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'original_url' => 'required|url|max:500',
        ]);

        $validated['slug'] = Str::random(8);

        TrackingLink::create($validated);

        return redirect()->route('tracking-links.index')
            ->with('success', 'Tracking link created successfully!');
    }

    public function show(TrackingLink $trackingLink)
    {
        $clicks = $trackingLink->clicks()->latest()->paginate(10);
        return view('tracking-links.show', compact('trackingLink', 'clicks'));
    }

    public function destroy(TrackingLink $trackingLink)
    {
        $trackingLink->delete();
        return redirect()->route('tracking-links.index')
            ->with('success', 'Tracking link deleted successfully!');
    }
}