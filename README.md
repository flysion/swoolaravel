# swoole-laravel
使用 swoole 作为 webserver 来搭载 laravel 框架。该组件只是对 swoole 进行最少干扰的封装，所以使用之前您需要对 swoole 和 Linux 网络编程进行一番了解

[swoole wiki](https://wiki.swoole.com/wiki/index/prid-1)

## 使用方法

安装

    composer require lee2son/swoole-laravel
    
注册服务（在 `app/Providers/AppServiceProvider.php` 中或新建一个 provider）

    use Lee2son\Laravoole\Server\WebSocket;
    use Lee2son\Laravoole\Server\Http;
    
    public function register()
    {
        $this->app->singleton(Server::class, function () {
            return new class extends WebSocket/* or Http */ {
                // coding ...
            };
        });
    }
    
    public function boot()
    {
        /**
         * @var WebSocket $server
         */
        $server = app('laravoole.server');

        $server->on('Start', function() {
            echo "hello swoole-laravel\n";
        });
    }
    
启动

    php artisan laravoole:service start
    
## 配置
在修改相关配置前，需要把配置模板复制到 laravle 框架的 config 目录下，运行如下命令即可：

    php artisan vendor:publish --tag=laravoole
    
复制完成后在 `config/webserver.php` 中修改配置