<?php 

namespace Flysion\Swoolaravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ServiceProvider extends ServiceProvider implements DeferrableProvider
{
    protected $commands = [

    ];

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot()
    {
        $this->commands($this->commands);
    }

    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [

        ];
    }
}