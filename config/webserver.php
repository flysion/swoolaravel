<?php
return [
    'host' => env('LARAVOOLE_HOST', '0.0.0.0'),

    'port' => env('LARAVOOLE_PORT', '9999'),

    /*
    |--------------------------------------------------------------------------
    | server 进程模式（ https://wiki.swoole.com/wiki/page/353.html ）
    |--------------------------------------------------------------------------
    |
    */

    'process_mode' => SWOOLE_PROCESS,

    'sock_type' => SWOOLE_SOCK_TCP,

    /*
    |--------------------------------------------------------------------------
    | server 选项（ see https://wiki.swoole.com/wiki/page/274.html ）
    |--------------------------------------------------------------------------
    |
    | 其中增加一个选项“process_name_prefix”，用于通过“swoole_set_process_name”方法设置进程名称：
    | {process_name_prefix}-master 主进程名称
    | {process_name_prefix}-manager 管理进程名称
    | {process_name_prefix}-worker-{worker_id}-task-{task_id} task 进程名称
    | {process_name_prefix}-worker-{worker_id}-event-{worker_id} event 进程名称
    |
    */

    'settings' => [
        'process_name_prefix' => 'swoole-laravoole-',
        'pid_file' => storage_path('server.pid'),
        'worker_num' => swoole_cpu_num() * 2,
        'task_worker_num' => swoole_cpu_num() * 2,
        'upload_tmp_dir' => storage_path('upload_tmp'),
    ]
];