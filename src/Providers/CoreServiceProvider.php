<?php

namespace Ahmeti\Core;

use Illuminate\Support\ServiceProvider;
use App\Modules\Core\Services\CoreService;

class CoreServiceProvider extends ServiceProvider
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
        $this->app->singleton('coreservice', function () {
            return new CoreService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['coreservice'];
    }
}
