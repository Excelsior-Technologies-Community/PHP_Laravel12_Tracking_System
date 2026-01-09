<?php

use App\Http\Controllers\TrackingLinkController;
use App\Http\Controllers\ClickController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Tracking Links Routes
Route::resource('tracking-links', TrackingLinkController::class)->except(['edit', 'update']);

// Click Tracking Route
Route::get('/track/{slug}', [ClickController::class, 'track'])->name('tracking.click');