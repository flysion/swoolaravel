# swoole-laravel

## laravel 集成到 swoole 中的注意点
1. 不要使用 `\Fruitcake\Cors\HandleCors::class` 中间件，在每次 Web 请求该中间件都会注册一个事件监听，最终导致内存耗尽：

        // vendor/fruitcake/laravel-cors/src/HandleCors.php
        
        $this->container->make('events')->listen(RequestHandled::class, function (RequestHandled $event) {
            $this->addHeaders($event->request, $event->response);
        });
        
    该中间件是自动开启的，在`\App\Http\Kernel`中可找到并关闭他
    
2. worker进程清理，原理见：

        # vendor/facade/ignition/src/IgnitionServiceProvider.php
        
        $queue->looping(function () {
            $this->app->get(Flare::class)->reset();

            if (config('flare.reporting.report_queries')) {
                $this->app->make(QueryRecorder::class)->reset();
            }

            $this->app->make(LogRecorder::class)->reset();

            $this->app->make(DumpRecorder::class)->reset();
        });
        
    在写日志、`dump`等的时候，该插件会保存记录，直到被清理。通过代码逻辑可知 queue 模式在每次 looping 时会作清理。但在命令行模式或 swoole 中不会清理，这将内存持续飙高
    
3. 使用 `predis` 而不是 `phpredis`，phpredis 会在主进程一启动就建立 socket 连接并通过 `fork` 共享给子进程，这将导致进程安全问题