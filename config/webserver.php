<?php
return [
    'enable_websocket' => true,

    'host' => env('LARAVOOLE_HOST', '0.0.0.0'),
    'port' => env('LARAVOOLE_PORT', '9999'),
    // see https://wiki.swoole.com/wiki/page/353.html
    'process_mode' => SWOOLE_PROCESS,
    'sock_type' => SWOOLE_SOCK_TCP,

    // see https://wiki.swoole.com/wiki/page/620.html
    'server_options' => [
        'master_name' => 'swoole-laravel-server-master',
        'manager_name' => 'swoole-laravel-server-manager',
        'task_name_prefix' => 'swoole-laravel-server-task-',
        'event_name_prefix' => 'swoole-laravel-server-event-',

        'worker_num' => 2,
        'task_worker_num' => 2,

        'upload_tmp_dir' => storage_path('upload_tmp'),
    ],
];