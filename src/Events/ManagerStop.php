<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 当管理进程结束时触发
 * onManagerStop 触发时，说明 Task 和 Worker 进程已结束运行，已被 Manager 进程回收。
 *
 * @link https://wiki.swoole.com/#/server/events?id=onmanagerstop
 */
class ManagerStop implements SwooleEvent
{
    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}