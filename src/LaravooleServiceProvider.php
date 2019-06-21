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
        // php artisan vendor:publish --tag=laravoole
        $this->registerPublishes();

        // load config
        $this->mergeConfigFrom(__DIR__ . '/../config/laravoole.php', 'laravoole');

        // register command
        $this->commands([Service::class]);

        // bind server
        $this->registerServer();

        // load route
        $this->loadRoutesFrom(base_path('routes/webserver.php'));
    }

    /**
     * get config from config/laravoole.php
     * @param $key
     * @param null $default
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        if($this->config === null) {
            $this->config = new Repository(config('laravoole'));
        }

        return $this->config->get($key, $default);
    }

    /**
     * create a HttpServer
     * @return HttpServer
     */
    protected function newHttpServer() : HttpServer
    {
        return new HttpServer(
            $this->config->get('host'),
            $this->config->get('port'),
            $this->config->get('server_options'),
            $this->config->get('process_mode'),
            $this->config->get('sock_type')
        );
    }

    /**
     * create a WebSocketServer
     * @return WebSocketServer
     */
    protected function newWebSocketServer() : WebSocketServer
    {
        return new WebSocketServer(
            $this->config->get('host'),
            $this->config->get('port'),
            $this->config->get('server_options'),
            $this->config->get('process_mode'),
            $this->config->get('sock_type')
        );
    }

    /**
     * register publishes
     * @return void
     */
    protected function registerPublishes()
    {
        $this->publishes([
            __DIR__ . '/../config/laravoole.php' => base_path('config/laravoole.php'),
            __DIR__ . '/../routes/websocket.php' => base_path('routes/websocket.php')
        ], 'laravoole');
    }

    /**
     * bind Server::class
     * alias laravoole.server
     * @return void
     */
    protected function registerServer()
    {
        if($this->config->get('enable_websocket', false)) {
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