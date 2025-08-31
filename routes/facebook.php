<?php

use Illuminate\Support\Facades\Route;

// Facebook Dashboard routes
Route::middleware(['auth', 'verified'])->prefix('facebook')->name('facebook.')->group(function () {
    Route::get('overview', [App\Http\Controllers\FacebookDashboardController::class, 'overview'])
        ->middleware(['permission.404:facebook.overview'])
        ->name('overview');

    Route::match(['GET','POST'], 'overview/ai-summary', [App\Http\Controllers\FacebookDashboardController::class, 'overviewAiSummary'])
        ->middleware(['permission.404:facebook.overview'])
        ->name('overview.ai-summary');

    Route::get('hierarchy', [App\Http\Controllers\FacebookDashboardController::class, 'hierarchy'])
        ->middleware(['permission.404:facebook.hierarchy'])
        ->name('hierarchy');

    Route::get('analytics', [App\Http\Controllers\FacebookDashboardController::class, 'analytics'])
        ->middleware(['permission.404:facebook.analytics'])
        ->name('analytics');

    Route::get('data-raw', [App\Http\Controllers\FacebookDashboardController::class, 'dataRaw'])
        ->middleware(['permission.404:facebook.data_raw'])
        ->name('data-raw');
});

// Facebook sync routes
Route::middleware(['auth', 'verified', 'permission.404:facebook.sync'])->group(function () {
    Route::post('dashboard/sync-facebook', [App\Http\Controllers\DashboardController::class, 'syncFacebook'])
        ->name('dashboard.sync-facebook');
    
    Route::match(['get', 'post'], 'facebook/sync/ads', [App\Http\Controllers\FacebookSyncController::class, 'syncAds'])
        ->name('facebook.sync.ads');
    
    Route::post('facebook/sync/ads-direct', [App\Http\Controllers\FacebookSyncController::class, 'syncAdsDirect'])
        ->name('facebook.sync.ads-direct');
    
    Route::get('facebook/sync/test', function() {
        return view('facebook.sync-test');
    })->name('facebook.sync.test');
    
    Route::get('facebook/sync/status', [App\Http\Controllers\FacebookSyncController::class, 'getSyncStatus'])
        ->name('facebook.sync.status');
    
    Route::get('facebook/sync/progress', [App\Http\Controllers\FacebookSyncController::class, 'getSyncProgress'])
        ->name('facebook.sync.progress');
    
    Route::post('facebook/sync/stop', [App\Http\Controllers\FacebookSyncController::class, 'stopSync'])
        ->name('facebook.sync.stop');
    
    Route::post('facebook/sync/reset', [App\Http\Controllers\FacebookSyncController::class, 'resetSync'])
        ->name('facebook.sync.reset');
});

// Facebook Data Management routes
Route::middleware(['auth', 'verified'])->prefix('facebook')->name('facebook.')->group(function () {
    Route::get('data-management', [App\Http\Controllers\FacebookDataController::class, 'index'])
        ->name('data-management.index');
    
    Route::get('data-management/page-data', [App\Http\Controllers\FacebookDataController::class, 'getPageData'])
        ->name('data-management.page-data');
    
    Route::get('data-management/posts', [App\Http\Controllers\FacebookDataController::class, 'getPostsByPage'])
        ->name('data-management.posts');
    
    Route::get('data-management/spending-stats', [App\Http\Controllers\FacebookDataController::class, 'getPostSpendingStats'])
        ->name('data-management.spending-stats');
    
    Route::get('data-management/ad-campaigns', [App\Http\Controllers\FacebookDataController::class, 'getAdCampaigns'])
        ->name('data-management.ad-campaigns');
    
    Route::get('data-management/ad-breakdowns', [App\Http\Controllers\FacebookDataController::class, 'getAdBreakdowns'])
        ->name('data-management.ad-breakdowns');
    
    Route::get('data-management/ad-insights', [App\Http\Controllers\FacebookDataController::class, 'getAdInsights'])
        ->name('data-management.ad-insights');
    
    Route::post('data-management/ai-summary', [App\Http\Controllers\FacebookDataController::class, 'getAiSummary'])
        ->name('data-management.ai-summary');
    
    Route::get('data-management/post/{postId}/page/{pageId}', [App\Http\Controllers\FacebookDataController::class, 'showPostDetail'])
        ->name('data-management.post-detail');
});
