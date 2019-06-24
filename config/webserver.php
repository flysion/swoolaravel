<?php
return [
    'host' => env('LARAVOOLE_HOST', '0.0.0.0'),
    'port' => env('LARAVOOLE_PORT', '9999'),
    // see https://wiki.swoole.com/wiki/page/353.html
    'process_mode' => SWOOLE_PROCESS,
    'sock_type' => SWOOLE_SOCK_TCP,

    // see https://wiki.swoole.com/wiki/page/620.html
    'settings' => [
        'process_name_prefix' => 'swoole-laravoole-',
        'worker_num' => swoole_cpu_num() * 2,
        'task_worker_num' => swoole_cpu_num() * 2,
        'upload_tmp_dir' => storage_path('upload_tmp'),
    ],

    // client table see https://wiki.swoole.com/wiki/page/257.html
    'client_table' => [
        'max_size' => 0,
        'columns' => [

        ],
    ],
];