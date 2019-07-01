<?php namespace Lee2son\Laravoole;

use Illuminate\Support\ServiceProvider;
use Lee2son\Laravoole\Console\Commands\Service;

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
        $this->mergeConfigFrom(__DIR__ . '/../config/webserver.php', 'webserver');
        $this->registerPublishes();
        $this->commands(Service::class);
        $this->loadRoute();
        $this->registerServer();
    }

    /**
     * 加载路由
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
     * 注册 publishes
     * 通过“php artisan vendor:publish --tag=laravoole”命令把默认配置复制到相应目录去
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
     * 为 Server::class 起别名
     * 开发者需要自己绑定实体类（在 provider 的 register 中）：
     * $this->app->singleton(Server::class, \Lee2son\Laravoole\Server\Http::class) or $this->app->singleton(Server::class, \Lee2son\Laravoole\Server\WebSocket::class)
     * 然后就可以使用了：
     * app('laravoole.server')->sendMessage();
     * @return void
     */
    protected function registerServer()
    {
        $this->app->alias(Server::class, 'laravoole.server');
    }
}