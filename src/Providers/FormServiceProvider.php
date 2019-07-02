<?php

namespace Ahmeti\Core;

use Illuminate\Support\ServiceProvider;
use App\Modules\Core\Services\FormService;

class FormServiceProvider extends ServiceProvider
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
        $this->app->singleton('formservice', function () {
            return new FormService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['formservice'];
    }
}
