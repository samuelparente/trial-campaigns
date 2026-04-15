<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ContactListController;
use App\Http\Controllers\Api\CampaignController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| NOTE: API versioning (e.g., /v1/) is intentionally omitted to
| strictly adhere to the trial specification requirements.
|
*/

// Contacts Endpoints
Route::prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index']);
    Route::post('/', [ContactController::class, 'store']);
    Route::post('/{contact}/unsubscribe', [ContactController::class, 'unsubscribe']);
});

// Contact Lists Endpoints
Route::prefix('contact-lists')->group(function () {
    Route::get('/', [ContactListController::class, 'index']);
    Route::post('/', [ContactListController::class, 'store']);
    Route::post('/{contactList}/contacts', [ContactListController::class, 'addContact']);
});

// Campaigns Endpoints
Route::prefix('campaigns')->group(function () {
    Route::get('/', [CampaignController::class, 'index']);
    Route::post('/', [CampaignController::class, 'store']);
    Route::get('/{campaign}', [CampaignController::class, 'show']);
    
    // Utilizing the previously fixed middleware to prevent dispatching non-drafts
    Route::post('/{campaign}/dispatch', [CampaignController::class, 'dispatch'])
        ->middleware('EnsureCampaignIsDraft');
});