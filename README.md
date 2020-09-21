# swoole-laravel

## laravel 集成到 swoole 中的注意点
1. 不要使用 `\Fruitcake\Cors\HandleCors::class` 中间件，在每次 Web 请求该中间件都会注册一个事件监听，最终导致内存耗尽：

        // vendor/fruitcake/laravel-cors/src/HandleCors.php
        
        $this->container->make('events')->listen(RequestHandled::class, function (RequestHandled $event) {
            $this->addHeaders($event->request, $event->response);
        });
        
    该中间件是自动开启的，在`\App\Http\Kernel`中可找到并关闭他

2. 使用 `predis` 而不是 `phpredis`，phpredis 会在主进程一启动就建立 socket 连接并通过 `fork` 共享给子进程，这将导致进程安全问题