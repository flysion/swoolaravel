<?php
return [
    'server' => [
        /**
         * 启动器
         * @see \Flysion\Swoolaravel\Launcher
         */
        'launcher' => null,

        /**
         * 服务监听地址
         * IPv4 使用 127.0.0.1 表示监听本机，0.0.0.0 表示监听所有地址
         * IPv6 使用::1 表示监听本机，:: (相当于 0:0:0:0:0:0:0:0) 表示监听所有地址
         * @link https://wiki.swoole.com/#/server/methods?id=__construct
         */
        'host' => '0.0.0.0',

        /**
         * 服务监听端口
         * 如果 sock_type 值为 SWOOLE_UNIX_DGRAM，此参数将被忽略
         * 监听小于 1024 端口需要 root 权限
         * 如果此端口被占用 start 时会失败
         * @link https://wiki.swoole.com/#/server/methods?id=__construct
         */
        'port' => 18888,

        /**
         * 服务运行模式
         * @link https://wiki.swoole.com/#/server/methods?id=__construct
         * @link https://wiki.swoole.com/#/learn?id=server%e7%9a%84%e4%b8%a4%e7%a7%8d%e8%bf%90%e8%a1%8c%e6%a8%a1%e5%bc%8f%e4%bb%8b%e7%bb%8d 两种运行模式介绍
         */
        'mode' => SWOOLE_PROCESS,

        /**
         * 服务类型；可选的值如下：
         *  SWOOLE_TCP/SWOOLE_SOCK_TCP tcp ipv4 socket
         *  SWOOLE_TCP6/SWOOLE_SOCK_TCP6 tcp ipv6 socket
         *  SWOOLE_UDP/SWOOLE_SOCK_UDP udp ipv4 socket
         *  SWOOLE_UDP6/SWOOLE_SOCK_UDP6 udp ipv6 socket
         *  SWOOLE_UNIX_DGRAM unix socket dgram
         *  SWOOLE_UNIX_STREAM unix socket stream
         *
         * @link https://wiki.swoole.com/#/server/methods?id=__construct
         */
        'sock_type' => SWOOLE_SOCK_TCP,

        /**
         * 日志通道 in config:logging.channels
         */
        'log' => null,

        /**
         * 装载器（在 start 服务之前调用）
         * @see \Flysion\Swoolaravel\Loaders\LoaderInterface
         */
        'loaders' => [

        ],

        /**
         * 事件引导器（在事件处理之前调用）
         * @see \Flysion\Swoolaravel\Bootstraps\BootstrapInterface
         */
        'bootstraps' => [

        ],

        /**
         * 事件清理器（在事件处理完毕之后调用）；可对当前进程空间进行清理
         * 在每一次进程处理完毕后使用 memory_get_usage() 记录内存是否比上一次多来判断是否有内存未释放
         * @see \Flysion\Swoolaravel\Bootstraps\BootstrapInterface
         */
        'cleaners' => [
            'task' => [

            ]
        ],

        /**
         * swoole server 配置
         * task_enable_coroutine 选项已经被禁用，默认是 false
         * task_use_object 选项已经被禁用；默认是 false
         * pid_file 选项已经被禁用；默认在 storage 目录下
         * 通过 open_http_protocol 选项控制是否将 HTTP 请求转发到 laravel；http server 将强制转发
         * @link https://wiki.swoole.com/#/server/setting
         */
        'setting' => [

        ],
    ],

    'http_server' => [
        /**
         * 启动器
         * @see \Flysion\Swoolaravel\Launcher
         */
        'launcher' => null,

        /**
         * 服务监听地址
         * IPv4 使用 127.0.0.1 表示监听本机，0.0.0.0 表示监听所有地址
         * IPv6 使用::1 表示监听本机，:: (相当于 0:0:0:0:0:0:0:0) 表示监听所有地址
         * @link https://wiki.swoole.com/#/server/methods?id=__construct
         */
        'host' => '0.0.0.0',

        /**
         * 服务监听端口
         * 如果 sock_type 值为 SWOOLE_UNIX_DGRAM，此参数将被忽略
         * 监听小于 1024 端口需要 root 权限
         * 如果此端口被占用 start 时会失败
         * @link https://wiki.swoole.com/#/server/methods?id=__construct
         */
        'port' => 18888,

        /**
         * 日志通道 in config:logging.channels
         */
        'log' => null,

        /**
         * 装载器（在 start 服务之前调用）
         * @see \Flysion\Swoolaravel\Loaders\LoaderInterface
         */
        'loaders' => [

        ],

        /**
         * 事件引导器（在事件处理之前调用）
         * @see \Flysion\Swoolaravel\Bootstraps\BootstrapInterface
         */
        'bootstraps' => [

        ],

        /**
         * 事件清理器（在事件处理完毕之后调用）；可对当前进程空间进行清理
         * 在每一次进程处理完毕后使用 memory_get_usage() 记录内存是否比上一次多来判断是否有内存未释放
         * @see \Flysion\Swoolaravel\Bootstraps\BootstrapInterface
         */
        'cleaners' => [
            'task' => [

            ],
            'request' => [

            ]
        ],

        /**
         * swoole http server 配置
         * task_enable_coroutine 选项已经被禁用，默认是 false
         * task_use_object 选项已经被禁用；默认是 false
         * pid_file 选项已经被禁用；默认在 storage 目录下
         * 通过 open_http_protocol 选项控制是否将 HTTP 请求转发到 laravel；http server 将强制转发
         * @link https://wiki.swoole.com/#/http_server?id=%e9%85%8d%e7%bd%ae%e9%80%89%e9%a1%b9
         */
        'setting' => [

        ],
    ],

    'websocket_server' => [
        /**
         * 启动器
         * @see \Flysion\Swoolaravel\Launcher
         */
        'launcher' => null,

        /**
         * 服务监听地址
         * IPv4 使用 127.0.0.1 表示监听本机，0.0.0.0 表示监听所有地址
         * IPv6 使用::1 表示监听本机，:: (相当于 0:0:0:0:0:0:0:0) 表示监听所有地址
         * @link https://wiki.swoole.com/#/server/methods?id=__construct
         */
        'host' => '0.0.0.0',

        /**
         * 服务监听端口
         * 如果 sock_type 值为 SWOOLE_UNIX_DGRAM，此参数将被忽略
         * 监听小于 1024 端口需要 root 权限
         * 如果此端口被占用 start 时会失败
         * @link https://wiki.swoole.com/#/server/methods?id=__construct
         */
        'port' => 18888,

        /**
         * 日志通道 in config:logging.channels
         */
        'log' => null,

        /**
         * 装载器（在 start 服务之前调用）
         * @see \Flysion\Swoolaravel\Loaders\LoaderInterface
         */
        'loaders' => [

        ],

        /**
         * 事件引导器（在事件处理之前调用）
         * @see \Flysion\Swoolaravel\Bootstraps\BootstrapInterface
         */
        'bootstraps' => [

        ],

        /**
         * 事件清理器（在事件处理完毕之后调用）；可对当前进程空间进行清理
         * 在每一次进程处理完毕后使用 memory_get_usage() 记录内存是否比上一次多来判断是否有内存未释放
         * @see \Flysion\Swoolaravel\Bootstraps\BootstrapInterface
         */
        'cleaners' => [
            'task' => [

            ],
            'request' => [

            ]
        ],

        /**
         * swoole websocket server 配置
         * task_enable_coroutine 选项已经被禁用，默认是 false
         * task_use_object 选项已经被禁用；默认是 false
         * pid_file 选项已经被禁用；默认在 storage 目录下
         * 通过 open_http_protocol 选项控制是否将 HTTP 请求转发到 laravel；http server 将强制转发
         * @link https://wiki.swoole.com/#/websocket_server?id=%e9%80%89%e9%a1%b9
         */
        'setting' => [

        ],
    ],
];