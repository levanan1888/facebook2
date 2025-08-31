<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('dashboard', [App\Http\Controllers\UnifiedDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'permission.404:dashboard.view'])
    ->name('dashboard');

Route::get('debug/campaigns', [App\Http\Controllers\DashboardController::class, 'debugCampaigns'])
    ->middleware(['auth', 'verified'])
    ->name('debug.campaigns');

// Analytics routes
Route::middleware(['auth', 'verified', 'permission.404:analytics'])->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('index');
});

// Include Facebook routes
require __DIR__.'/facebook.php';

// Facebook Data Management routes
Route::middleware(['auth', 'verified'])->prefix('facebook')->name('facebook.')->group(function () {
    Route::get('data-management', [App\Http\Controllers\FacebookDataController::class, 'index'])->name('data-management.index');
    Route::get('data-management/posts', [App\Http\Controllers\FacebookDataController::class, 'getPostsByPage'])->name('data-management.posts');
    Route::get('data-management/post/{postId}/page/{pageId}', [App\Http\Controllers\FacebookDataController::class, 'showPostDetail'])->name('data-management.post-detail');
    Route::get('data-management/spending-stats', [App\Http\Controllers\FacebookDataController::class, 'getPostSpendingStats'])->name('data-management.spending-stats');
    Route::get('data-management/page-data', [App\Http\Controllers\FacebookDataController::class, 'getPageData'])->name('data-management.page-data');
    Route::get('data-management/ad-campaigns', [App\Http\Controllers\FacebookDataController::class, 'getAdCampaigns'])->name('data-management.ad-campaigns');
    Route::get('data-management/ad-breakdowns', [App\Http\Controllers\FacebookDataController::class, 'getAdBreakdowns'])->name('data-management.ad-breakdowns');
    Route::get('data-management/ad-insights', [App\Http\Controllers\FacebookDataController::class, 'getAdInsights'])->name('data-management.ad-insights');
});

// Include API routes
require __DIR__.'/api.php';

// Test route để kiểm tra trang 404
Route::get('/test-404', function () {
    abort(404);
})->name('test.404');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    // Website settings (require permission)
    Route::middleware(['permission.404:manage settings'])->group(function () {
        Route::get('settings/website', [\App\Http\Controllers\Settings\WebsiteController::class, 'index'])->name('settings.website');
        Route::put('settings/website', [\App\Http\Controllers\Settings\WebsiteController::class, 'update'])->name('settings.website.update');
    });
});

// Admin routes
Route::middleware(['auth', 'verified', 'track.login'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
    Route::resource('login-activities', \App\Http\Controllers\Admin\LoginActivityController::class);
    Route::post('login-activities/clear-old', [\App\Http\Controllers\Admin\LoginActivityController::class, 'clearOld'])->name('login-activities.clear-old');
});

require __DIR__.'/auth.php';
