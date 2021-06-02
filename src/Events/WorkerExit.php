<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 仅在开启 reload_async 特性后有效。参见 如何正确的重启服务
 *
 * @link https://wiki.swoole.com/#/server/events?id=onworkerexit onWorkerExit
 * @link https://wiki.swoole.com/#/question/use?id=swoole%e5%a6%82%e4%bd%95%e6%ad%a3%e7%a1%ae%e7%9a%84%e9%87%8d%e5%90%af%e6%9c%8d%e5%8a%a1 如何正确的重启服务
 */
class WorkerExit implements SwooleEvent
{
    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var int Worker 进程 id（非进程的 PID）
     */
    public $workerId;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     * @param int $workerId 进程 id（非进程的 PID）
     */
    public function __construct($server, $workerId)
    {
        $this->server = $server;
        $this->workerId = $workerId;
    }
}