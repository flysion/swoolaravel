# swoole-laravel

## laravel 集成到 swoole 中的注意点
+ 使用 `predis` 而不是 `phpredis`，phpredis 会在主进程一启动就建立 socket 连接并通过 `fork` 共享给子进程，这将导致进程安全问题