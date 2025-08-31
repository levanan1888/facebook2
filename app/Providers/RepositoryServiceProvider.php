<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\FacebookAdRepository;
use App\Repositories\FacebookCampaignRepository;
use App\Models\FacebookAd;
use App\Models\FacebookCampaign;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(FacebookAdRepository::class, function ($app) {
            return new FacebookAdRepository(new FacebookAd());
        });

        $this->app->bind(FacebookCampaignRepository::class, function ($app) {
            return new FacebookCampaignRepository(new FacebookCampaign());
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
