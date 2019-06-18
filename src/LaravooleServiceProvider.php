<?php
namespace Lee2son\Laravoole;

use Illuminate\Support\ServiceProvider;
use Lee2son\Laravoole\Commands\Service;

class LaravooleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravoole.php', 'laravoole');
    }

    public function register()
    {
        $this->commands([Service::class]);
    }
}