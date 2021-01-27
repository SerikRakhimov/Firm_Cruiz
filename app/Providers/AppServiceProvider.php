<?php

namespace App\Providers;

use App\Models\Item;
use App\Models\Project;
use App\Observers\ItemObserver;
use App\Observers\ProjectObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Schema::defaultStringLength(255);
        //
        Item::observe(ItemObserver::class);
        Project::observe(ProjectObserver::class);
    }
}
