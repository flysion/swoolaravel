<?php

namespace Lee2son\Swoolaravel\Foundation;

use Illuminate\Support\Traits\Macroable;

class Application extends \Illuminate\Foundation\Application
{
    /**
     * 判断当前程序是否在SWOOLE中运行
     * @return bool
     */
    public function runningInSwoole()
    {
        if (isset($_ENV['APP_RUNNING_IN_SWOOLE'])) {
            return $_ENV['APP_RUNNING_IN_SWOOLE'] === 'true';
        }

        return false;
    }
}