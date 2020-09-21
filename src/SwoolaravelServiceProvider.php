<?php 

namespace Lee2son\Swoolaravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SwoolaravelServiceProvider extends ServiceProvider implements DeferrableProvider
{
    protected $commands = [
        \Lee2son\Swoolaravel\Console\Commands\Start::class
    ];

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/server_options.php', 'server_options');
        $this->commands($this->commands);
        $this->registerPublishes();
    }

    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {

    }

    /**
     * 注册 publishes
     * 通过“php artisan vendor:publish --tag=swoolaravel”命令把默认配置复制到相应目录去
     *
     * @return void
     */
    protected function registerPublishes()
    {
        $this->publishes([
            __DIR__ . '/../config/swoolaravel.php' => base_path('config/swoolaravel.php'),
            __DIR__ . '/../config/server_options.php' => base_path('config/server_options.php')
        ], 'swoolaravel');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'swoolaravel:start'
        ];
    }
}