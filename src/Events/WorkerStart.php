<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 此事件在 Server 正常结束时发生【主进程】
 *
 * 在此之前 Swoole\Server 已进行了如下操作:
 *  1.已关闭所有 Reactor 线程、HeartbeatCheck 线程、UdpRecv 线程
 *  2.已关闭所有 Worker 进程、 Task 进程、User 进程
 *  3.已 close 所有 TCP/UDP/UnixSocket 监听端口
 *  4.已关闭主 Reactor
 *
 * 请勿在 onShutdown 中调用任何异步或协程相关 API，触发 onShutdown 时底层已销毁了所有事件循环设施。
 *
 * @link https://wiki.swoole.com/#/server/events?id=onshutdown onShutdown
 */
class WorkerStart
{
    /**
     * swoole 事件名称
     */
    const name = 'workerStart';

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
     * @param int $workerId Worker 进程 id（非进程的 PID）
     */
    public function __construct($server, $workerId)
    {
        $this->server = $server;
        $this->workerId = $workerId;
    }
}