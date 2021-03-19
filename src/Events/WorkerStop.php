<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 此事件在 Worker 进程终止时发生。在此函数中可以回收 Worker 进程申请的各类资源。【Worker进程】
 *
 * 注意：
 *  1.进程异常结束，如被强制 kill、致命错误、core dump 时无法执行 onWorkerStop 回调函数。
 *  2.请勿在 onWorkerStop 中调用任何异步或协程相关 API，触发 onWorkerStop 时底层已销毁了所有事件循环设施。
 *
 * @link https://wiki.swoole.com/#/server/events?id=onworkerstop onWorkerStop
 */
class WorkerStop
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
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     * @param int $workerId Worker 进程 id（非进程的 PID）
     */
    public function __construct($server, $workerId)
    {
        $this->server = $server;
        $this->workerId = $workerId;
    }
}