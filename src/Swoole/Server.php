<?php

namespace Flysion\Swoolaravel\Swoole;

use Flysion\Swoolaravel\Events\Start;
use Flysion\Swoolaravel\Events\ManagerStart;
use Flysion\Swoolaravel\Events\WorkerStart;

/**
 * 事件执行顺序：
 *  1.所有事件回调均在 $server->start 后发生
 *  2.服务器关闭程序终止时最后一次事件是 onShutdown
 *  3.服务器启动成功后，onStart/onManagerStart/onWorkerStart 会在不同的进程内并发执行
 *  4.onReceive/onConnect/onClose 在 Worker 进程中触发
 *  5.Worker/Task 进程启动 / 结束时会分别调用一次 onWorkerStart/onWorkerStop
 *  6.onTask 事件仅在 task 进程中发生
 *  7.onFinish 事件仅在 worker 进程中发生
 *  8.onStart/onManagerStart/onWorkerStart 3 个事件的执行顺序是不确定的
 *
 * @link https://wiki.swoole.com/#/server/tcp_init
 * @link https://wiki.swoole.com/#/server/events?id=%e4%ba%8b%e4%bb%b6%e6%89%a7%e8%a1%8c%e9%a1%ba%e5%ba%8f 事件执行顺序
 * @mixin \Swoole\Server
 */
class Server extends \Swoole\Server
{
    use \Flysion\Swoolaravel\Server;

    /**
     * @param \Illuminate\Events\Dispatcher|null $events
     * @param string $host
     * @param int $port
     * @param int $mode
     * @param int $sockType
     */
    public function __construct($events = null, $host = '0.0.0.0', $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP)
    {
        parent::__construct($host, $port, $mode, $sockType);
        $this->events = $events ?? $this->createEvents();
    }

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    protected function createEvents()
    {
        return app('events');
    }
}