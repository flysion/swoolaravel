# swoole-laravel
使用 swoole 作为 webserver 来搭载 laravel 框架。该组件只是对 swoole 进行最少干扰的封装，所以使用之前您需要对 swoole 和 Linux 网络编程进行一番了解

[swoole wiki](https://wiki.swoole.com/) |
[swoole github](https://github.com/swoole/swoole-src)

## 使用方法

1. 安装

        composer require lee2son/swoole-laravel
    
2. 注册服务（在 `app/Providers/AppServiceProvider.php` 中或新建一个 provider）

        use Lee2son\Swoolaravel\Swoole\Http\Server as HttpServer;
        use Lee2son\Swoolaravel\Swoole\WebSocket\Server as WebSocketServer;
        
        public function register()
        {
            $this->app->singleton('swoolaravel.server', function () {
                $server = new class extends WebSocketServer/* or HttpServer */ {
                    public function __construct()
                    {
                        $host = '127.0.0.1';
                        $port = '9999';
                        $mode = SWOOLE_PROCESS;
                        $sock_type = SWOOLE_SOCK_TCP;
    
                        parent::__construct($host, $port, $mode, $sock_type);
    
                        $this->on('Request'); // 开启HTTP支持，如果继承自 HttpServer 就不需要加这一句
                    }
    
                    // TODO 重写方法达到特殊目的
                };
    
                $server->on('Start', function() { echo "hello swoolaravel\n"; });
                // ... 绑定事件
    
                return $server;
            });
        }
        
3. 修改 `bootstrap/app.php`

        $app = new Lee2son\Swoolaravel\Foundation\Application(
            $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
        );
    
4. 启动

        php artisan swoolaravel:server swoolaravel.server start
    
*启动完成会看到控制台打印`hello swoolaravel`；端口是`9999`，访问 `http://127.0.0.1:9999` 即可访问`/`路由*

## 使用说明

- 在`worker`进程中注册了一个单例类`\Lee2son\Swoolaravel\Swoole\Worker`，该类在 worker 进程整个生命周期不会销毁，可用来做些全局的保存工作，例如设置一个全局变量：

        app('swoolaravel.worker')->set('startTime', time());
    
*代码并不复杂且有详尽的注释，开发者自行查阅源代码进一步开拓使用方法*