<?php

return [
    'host' => [
        'desc' => '服务监听地址；IPv4 使用 127.0.0.1 表示监听本机；0.0.0.0 表示监听所有地址；IPv6 使用::1 表示监听本机；:: (相当于 0:0:0:0:0:0:0:0) 表示监听所有地址',
        'shortname' => 'H',
    ],
    'port' => [
        'desc' => '服务监听端口；如果 sock_type 值为 SWOOLE_UNIX_DGRAM/SWOOLE_UNIX_STREAM 此参数将被忽略',
        'formatter' => 'intval',
        'shortname' => 'p',
    ],
    'log' => [
        'desc' => '日志通道 in config:logging.channels',
    ],
    'process_name_prefix' => [
        'desc' => '进程名称前缀',
    ],
    'setting.reactor_num' => [
        'desc' => '启动的 Reactor 线程数；【默认值：CPU 核数】；见 https://wiki.swoole.com/#/server/setting?id=reactor_num',
        'formatter' => 'intval'
    ],
    'setting.worker_num' => [
        'desc' => '启动的 Worker 进程数；【默认值：CPU 核数】；见 https://wiki.swoole.com/#/server/setting?id=worker_num',
        'formatter' => 'intval'
    ],
    'setting.max_request' => [
        'desc' => 'worker 进程的最大任务数；见 https://wiki.swoole.com/#/server/setting?id=max_request',
        'formatter' => 'intval'
    ],
    'setting.max_connection' => [
        'desc' => '最大允许的连接数；【默认值：ulimit -n】；见 https://wiki.swoole.com/#/server/setting?id=max_conn-max_connection',
        'formatter' => 'intval'
    ],
    'setting.task_worker_num' => [
        'desc' => 'Task 进程的数量；未配置则不启动 Task 进程；见 https://wiki.swoole.com/#/server/setting?id=task_worker_num',
        'formatter' => 'intval'
    ],
    'setting.task_max_request' => [
        'desc' => 'Task 进程的最大任务数；见 https://wiki.swoole.com/#/server/setting?id=task_max_request',
        'formatter' => 'intval'
    ],
    'setting.task_tmpdir' => [
        'desc' => 'Task 的数据临时目录；相对路径相对于 storage 目录；见 https://wiki.swoole.com/#/server/setting?id=task_tmpdir',
        'formatter' => 'storage_path'
    ],
    'setting.daemonize' => [
        'desc' => '守护进程化；见 https://wiki.swoole.com/#/server/setting?id=daemonize',
        'value_none' => true,
    ],
    'setting.log_file' => [
        'desc' => 'Swoole 错误日志文件；相对路径相对于 storage 目录；见 https://wiki.swoole.com/#/server/setting?id=log_file',
        'formatter' => 'storage_path'
    ],
    'setting.log_level' => [
        'desc' => 'Server 错误日志打印的等级，范围是 0-6；低于 log_level 设置的日志信息不会抛出；见 https://wiki.swoole.com/#/server/setting?id=log_level',
        'formatter' => 'constant',
    ],
    'setting.open_cpu_affinity' => [
        'desc' => 'CPU 亲和性设置；见 https://wiki.swoole.com/#/server/setting?id=open_cpu_affinity',
        'value_none' => true,
    ],
    'setting.ssl_cert_file' => [
        'desc' => 'SSL 隧道加密证书文件地址；见 https://wiki.swoole.com/#/server/setting?id=ssl_cert_file',
    ],
    'setting.ssl_method' => [
        'desc' => 'OpenSSL 隧道加密的算法；见 https://wiki.swoole.com/#/server/setting?id=ssl_method',
        'formatter' => 'constant',
    ],
    'setting.ssl_ciphers' => [
        'desc' => 'openssl 加密算法；设置为空字符串时，由 openssl 自行选择加密算法；见 https://wiki.swoole.com/#/server/setting?id=ssl_ciphers',
    ],
    'setting.ssl_verify_peer' => [
        'desc' => '服务 SSL 设置验证对端证书；见 https://wiki.swoole.com/#/server/setting?id=ssl_verify_peer',
        'value_none' => true,
    ],
    'setting.user' => [
        'desc' => 'Worker/TaskWorker 子进程的所属用户；【默认值：执行脚本用户】；见 https://wiki.swoole.com/#/server/setting?id=user',
    ],
    'setting.group' => [
        'desc' => 'Worker/TaskWorker 子进程的进程用户组；【默认值：执行脚本用户组】；见 https://wiki.swoole.com/#/server/setting?id=group',
    ],
    'setting.request_slowlog_file' => [
        'desc' => '开启请求慢日志；相对路径相对于 storage 目录；见 https://wiki.swoole.com/#/server/setting?id=request_slowlog_file',
        'formatter' => 'storage_path'
    ],

    // http server

    'setting.upload_tmp_dir' => [
        'desc' => '上传文件的临时目录。目录最大长度不得超过 220 字节；相对路径相对于 storage 目录；见 https://wiki.swoole.com/#/http_server?id=upload_tmp_dir',
        'formatter' => 'storage_path'
    ],
    'setting.enable_static_handler' => [
        'desc' => '开启静态文件请求处理功能；见 https://wiki.swoole.com/#/http_server?id=enable_static_handler',
        'value_none' => true,
    ],
];