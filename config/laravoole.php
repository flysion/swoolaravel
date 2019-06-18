<?php
return [
    'host' => env('LARAVOOLE_HOST', '0.0.0.0'),
    'port' => env('LARAVOOLE_PORT', '9092'),

    // see https://wiki.swoole.com/wiki/page/353.html
    'process_type' => SWOOLE_PROCESS,

    // see https://wiki.swoole.com/wiki/page/620.html
    'server_options' => [
        'upload_tmp_dir' => env('LARAVOOLE_TMP_DIR', '/tmp'),
    ],

    'client_options' => [
        'max_size' => 100000,
        'columns' => [

        ]
    ]
];