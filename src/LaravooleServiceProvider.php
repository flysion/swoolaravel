<?php namespace Lee2son\Swoolaravel;

use Illuminate\Support\ServiceProvider;

class SwoolaravelServiceProvider extends ServiceProvider
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
        $this->mergeConfigFrom(__DIR__ . '/../config/swoolaravel.php', 'swoolaravel');
        $this->registerPublishes();
        $this->commands(\Lee2son\Swoolaravel\Console\Commands\Server::class);
        $this->loadRoute();
        $this->registerServer();
    }

    /**
     * 加载路由
     * @return void
     */
    protected function loadRoute()
    {
        $route_file = base_path('routes/swoolaravel.php');
        if(file_exists($route_file))
        {
            $this->loadRoutesFrom($route_file);
        }
    }

    /**
     * 注册 publishes
     * 通过“php artisan vendor:publish --tag=swoolaravel”命令把默认配置复制到相应目录去
     * @return void
     */
    protected function registerPublishes()
    {
        $this->publishes([
            __DIR__ . '/../config/swoolaravel.php' => base_path('config/swoolaravel.php'),
            __DIR__ . '/../routes/swoolaravel.php' => base_path('routes/swoolaravel.php')
        ], 'swoolaravel');
    }

    /**
     * 为 Server::class 起别名
     * 开发者需要自己绑定实体类（在 provider 的 register 中）：
     * $this->app->singleton(Server::class, \Lee2son\Swoolaravel\Server\Http::class)
     * $this->app->singleton(Server::class, \Lee2son\Swoolaravel\Server\WebSocket::class)
     * 然后就可以使用了：
     * app('swoolaravel.server')->start();
     * @return void
     */
    protected function registerServer()
    {
        $this->app->alias(Server::class, 'swoolaravel.server');
    }
}