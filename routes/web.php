<?php

use App\Http\Controllers\TrackingLinkController;
use App\Http\Controllers\ClickController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});



// Active Tracking Links
Route::resource(
    'tracking-links',
    TrackingLinkController::class
)
    ->except([
        'edit',
        'update'
    ]);



// Click Tracking
Route::get(
    '/track/{slug}',
    [ClickController::class, 'track']
)
    ->name('tracking.click');



// Trash Routes

Route::get(
    '/tracking-links-trash',
    [TrackingLinkController::class, 'trash']
)
    ->name('tracking-links.trash');



Route::put(
    '/tracking-links/{id}/restore',
    [TrackingLinkController::class, 'restore']
)
    ->name('tracking-links.restore');



Route::delete(
    '/tracking-links/{id}/force-delete',
    [TrackingLinkController::class, 'forceDelete']
)
    ->name('tracking-links.forceDelete');
