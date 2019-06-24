<?php namespace Lee2son\Laravoole;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use \Lee2son\Laravoole\Http\WebSocket\Server as WebSocketServer;
use \Lee2son\Laravoole\Http\Server as HttpServer;
use Lee2son\Laravoole\Commands\Service;

class LaravooleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        // load config
        $this->mergeConfigFrom(__DIR__ . '/../config/webserver.php', 'webserver');

        // php artisan vendor:publish --tag=laravoole
        $this->registerPublishes();

        // register command
        $this->commands(Service::class);

        // load route
        $this->loadRoute();

        // bind server
        $this->registerServer();
    }

    /**
     * load route
     * @return void
     */
    protected function loadRoute()
    {
        $route_file = base_path('routes/webserver.php');
        if(file_exists($route_file))
        {
            $this->loadRoutesFrom($route_file);
        }
    }

    /**
     * register publishes
     * @return void
     */
    protected function registerPublishes()
    {
        $this->publishes([
            __DIR__ . '/../config/webserver.php' => base_path('config/webserver.php'),
            __DIR__ . '/../routes/webserver.php' => base_path('routes/webserver.php')
        ], 'laravoole');
    }

    /**
     * bind Server::class, alias is laravoole.server
     * @return void
     */
    protected function registerServer()
    {
        $this->app->alias(Server::class, 'laravoole.server');
    }
}