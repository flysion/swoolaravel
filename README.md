# swoole-laravel
使用 swoole 作为 webserver 来搭载 laravel 框架。该组件只是对 swoole 进行最少干扰的封装，所以使用之前您需要对 swoole 和 Linux 网络编程进行一番了解

[swoole wiki](https://wiki.swoole.com/) |
[swoole github](https://github.com/swoole/swoole-src)

## 使用方法

1. 安装

        composer require lee2son/swoole-laravel
    
2. 注册服务（在 `app/Providers/AppServiceProvider.php` 中或新建一个 provider）

        use Lee2son\Laravoole\Server\WebSocket;
        use Lee2son\Laravoole\Server\Http;
        
        public function register()
        {
            $this->app->singleton(Server::class, function () {
                $server = new class extends WebSocket/* or Http */ {
                    // 重写方法达到特殊目的
                };
    
                $server->on('Start', function() { echo "onStart\n"; });
                // ... 绑定事件
    
                return $server;
            });
        }
    
3. 启动

        php artisan laravoole:server start
    
*启动完成会看到控制台打印“hello swoole-laravel”；默认端口是“9999”，访问 http://127.0.0.1:9999 即可访问“/”路由*
    
## 配置
在修改相关配置前，需要把配置模板复制到 laravle 框架的 config 目录下，运行如下命令即可：

    php artisan vendor:publish --tag=laravoole
    
复制完成后在 `config/webserver.php` 中修改配置


*代码并不复杂且有详尽的注释，开发者自行查阅源代码进一步开拓使用方法*

## 开发计划
针对 WebSocket 的开发计划
1. 增加消息中间件，封装服务器间通信