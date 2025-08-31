<?php

use Illuminate\Support\Facades\Route;

// API for sync progress
Route::middleware(['auth', 'verified', 'permission.404:facebook.sync'])->prefix('api')->name('api.')->group(function () {
    // Facebook Ads sync
    Route::post('sync/ads', [App\Http\Controllers\Api\SyncController::class, 'syncAds'])->name('sync.ads');
    Route::get('sync/status/{syncId}', [App\Http\Controllers\Api\SyncController::class, 'syncStatus'])->name('sync.status');
    
    // API status check
    Route::get('sync/status', [App\Http\Controllers\Api\SyncController::class, 'checkApiStatus'])->name('sync.api.status');
});

// API for unified data and comparison
Route::middleware(['auth', 'verified', 'permission.404:dashboard.analytics'])->prefix('api/dashboard')->name('api.dashboard.')->group(function () {
    Route::get('unified-data', [App\Http\Controllers\Api\DashboardApiController::class, 'getUnifiedData'])->name('unified-data');
    Route::get('comparison-data', [App\Http\Controllers\Api\DashboardApiController::class, 'getComparisonData'])->name('comparison-data');
    Route::get('filtered-data', [App\Http\Controllers\Api\DashboardApiController::class, 'getFilteredData'])->name('filtered-data');
    Route::get('data-sources-status', [App\Http\Controllers\Api\DashboardApiController::class, 'getDataSourcesStatus'])->name('data-sources-status');
    Route::post('refresh-cache', [App\Http\Controllers\Api\DashboardApiController::class, 'refreshCache'])->name('refresh-cache');
    Route::get('overview-data', [App\Http\Controllers\Api\DashboardApiController::class, 'getOverviewData'])->name('overview-data');
    Route::get('analytics-data', [App\Http\Controllers\Api\DashboardApiController::class, 'getAnalyticsData'])->name('analytics-data');
    Route::get('hierarchy-data', [App\Http\Controllers\Api\DashboardApiController::class, 'getHierarchyData'])->name('hierarchy-data');
});

// API for hierarchy
Route::middleware(['auth', 'verified', 'permission.404:facebook.hierarchy.api'])->prefix('api/hierarchy')->name('api.hierarchy.')->group(function () {
    Route::get('businesses', [App\Http\Controllers\Api\HierarchyController::class, 'getBusinesses'])->name('businesses');
    Route::get('accounts', [App\Http\Controllers\Api\HierarchyController::class, 'getAccounts'])->name('accounts');
    Route::get('campaigns', [App\Http\Controllers\Api\HierarchyController::class, 'getCampaigns'])->name('campaigns');
    Route::get('adsets', [App\Http\Controllers\Api\HierarchyController::class, 'getAdSets'])->name('adsets');
    Route::get('ads', [App\Http\Controllers\Api\HierarchyController::class, 'getAds'])->name('ads');
    Route::get('posts', [App\Http\Controllers\Api\HierarchyController::class, 'getPosts'])->name('posts');
});
