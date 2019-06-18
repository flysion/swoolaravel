<?php
namespace Lee2son\Laravoole;

use Illuminate\Support\ServiceProvider;
use Lee2son\Laravoole\Commands\Service;

class LaravelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/swoole_server.php', 'swoole_server');
    }

    public function register()
    {
        $this->commands([Service::class]);
    }
}