<?php 

namespace Flysion\Swoolaravel;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
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
        $this->publishes([
            __DIR__ . '/../config/swoolaravel.php' => $this->app->configPath('swoolaravel.php')
        ], 'swoolaravel');

        $this->mergeConfigFrom(__DIR__ . '/../config/swoolaravel.php', 'swoolaravel');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}