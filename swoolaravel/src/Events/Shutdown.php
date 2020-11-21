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
class Shutdown implements SwooleEvent
{
    /**
     * 事件触发之前
     */
    const before = self::class . ':before';

    /**
     * 事件触发之后
     */
    const after = self::class . ':after';

    /**
     * swoole 事件名称
     */
    const name = 'shutdown';

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