<?php
namespace Lee2son\Laravoole;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use \Lee2son\Laravoole\Http\WebSocket\Server as WebSocketServer;
use \Lee2son\Laravoole\Http\Server as HttpServer;
use Lee2son\Laravoole\Commands\Service;

class LaravooleServiceProvider extends ServiceProvider
{
    /**
     * @var Repository
     */
    protected $config = null;

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
        $this->mergeConfigFrom(__DIR__ . '/../config/laravoole.php', 'laravoole');

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
     * get config from config/laravoole.php
     * @param $key
     * @param null $default
     * @return mixed
     */
    protected function config($key = null, $default = null)
    {
        if($this->config === null) {
            $this->config = new Repository(config('laravoole'));
        }

        if(!$key) return $this->config;

        return $this->config->get($key, $default);
    }

    /**
     * create a HttpServer
     * @return HttpServer
     */
    protected function newHttpServer() : HttpServer
    {
        return new HttpServer(
            $this->config('host'),
            $this->config('port'),
            $this->config('server_options'),
            $this->config('process_mode'),
            $this->config('sock_type')
        );
    }

    /**
     * create a WebSocketServer
     * @return WebSocketServer
     */
    protected function newWebSocketServer() : WebSocketServer
    {
        return new WebSocketServer(
            $this->config('host'),
            $this->config('port'),
            $this->config('server_options'),
            $this->config('process_mode'),
            $this->config('sock_type')
        );
    }

    /**
     * load route
     * @return void
     */
    protected function loadRoute()
    {
        $route_file = base_path('routes/webserver.php');
        $this->loadRoutesFrom($route_file);
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
            __DIR__ . '/../config/laravoole.php' => base_path('config/laravoole.php'),
            __DIR__ . '/../routes/webserver.php' => base_path('routes/webserver.php')
        ], 'laravoole');
    }

    /**
     * bind Server::class, alias is laravoole.server
     * @return void
     */
    protected function registerServer()
    {
        if($this->config('enable_websocket', false)) {
            $this->app->singleton(Server::class, function () {
                return $this->newWebSocketServer();
            });
        } else {
            $this->app->singleton(Server::class, function () {
                return $this->newHttpServer();
            });
        }

        $this->app->alias(Server::class, 'laravoole.server');
    }
}