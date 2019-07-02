<?php

namespace Ahmeti\Core\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Core\Services\ResponseService;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('responseservice', function () {
            return new ResponseService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['responseservice'];
    }
}
