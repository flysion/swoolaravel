# swoole-laravel
使用 swoole 作为 webserver 来搭载 laravel 框架。该组件只是对 swoole 进行最少干扰的封装，所以使用之前您需要对 swoole 和 Linux 网络编程进行一番了解

[swoole wiki](https://wiki.swoole.com/) |
[swoole github](https://github.com/swoole/swoole-src)

## 使用方法

1. 安装

        composer require lee2son/swoole-laravel
    
2. 注册服务（在 `app/Providers/AppServiceProvider.php` 中或新建一个 provider）

        use Lee2son\Swoolaravel\Swoole\WebSocket\Server as WebSocketServer;
        use Lee2son\Swoolaravel\Server\Http\Server as HttpServer;
        
        public function register()
        {
            $host = '127.0.0.1';
            $port = '9999';
            $this->app->singleton('swoolaravel.server', function () {
                $server = new class($host, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP) extends WebSocketServer/* or HttpServer */ {
                    // 重写方法达到特殊目的
                };
    
                $server->on('Start', function() { echo "hello swoolaravel\n"; });
                // ... 绑定事件
    
                return $server;
            });
        }
    
3. 启动

        php artisan swoolaravel:server swoolaravel.server start
    
*启动完成会看到控制台打印`hello swoolaravel`；端口是`9999`，访问 `http://127.0.0.1:9999` 即可访问`/`路由*

*代码并不复杂且有详尽的注释，开发者自行查阅源代码进一步开拓使用方法*

## 开发计划
针对 WebSocket 的开发计划
1. 增加消息中间件，封装服务器间通信