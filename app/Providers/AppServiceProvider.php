<?php

namespace App\Providers;

use App\Models\Item;
use App\Models\Project;
use App\Models\Base;
use App\Observers\ProjectObserver;
use App\Observers\BaseObserver;
use App\Observers\ItemObserver;
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
        Project::observe(ProjectObserver::class);
        Base::observe(BaseObserver::class);
        Item::observe(ItemObserver::class);

    }
}
