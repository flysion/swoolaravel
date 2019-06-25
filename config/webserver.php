<?php
return [
    'host' => env('LARAVOOLE_HOST', '0.0.0.0'),
    'port' => env('LARAVOOLE_PORT', '9999'),

    /*
    |--------------------------------------------------------------------------
    | server 进程模式（ https://wiki.swoole.com/wiki/page/353.html ）
    |--------------------------------------------------------------------------
    |
    | SWOOLE_BASE 模式下不会主动触发 onStart 事件，亦没有 master 进程
    |
    */

    'process_mode' => SWOOLE_PROCESS,

    'sock_type' => SWOOLE_SOCK_TCP,

    /*
    |--------------------------------------------------------------------------
    | server 选项（ see https://wiki.swoole.com/wiki/page/274.html ）
    |--------------------------------------------------------------------------
    |
    | 其中增加一个选项：process_name_prefix，用于通过 swoole_set_process_name 设置进程名称：
    | {swoole_set_process_name}-master 主进程名称
    | {swoole_set_process_name}-manager 管理进程名称
    | {swoole_set_process_name}-task-{worker_id} task 进程名称
    | {swoole_set_process_name}-event-{worker_id} event 进程名称
    | {swoole_set_process_name}-mq 用于处理消息的进程的名称，该进程会订阅 redis
    |
    */

    'settings' => [
        'process_name_prefix' => 'swoole-laravoole-',
        'worker_num' => swoole_cpu_num() * 2,
        'task_worker_num' => swoole_cpu_num() * 2,
        'upload_tmp_dir' => storage_path('upload_tmp'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 启用 websocket 时会用到，设置 client 的属性（ https://wiki.swoole.com/wiki/page/257.html ）
    |--------------------------------------------------------------------------
    |
    | 如果 columns 为空数组则不会为每个连接创建 client
    | 通过 app('laravoole.server')->clients 可以获得所有的 client
    |
    */

    'client_table' => [
        'max_size' => 0,
        'columns' => [

        ],
    ],
];