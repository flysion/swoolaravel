<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 有新的连接进入时，在 worker 进程中回调。
 *
 * dispatch_mode = 1/3模式下：
 *  1.在此模式下 onConnect/onReceive/onClose 可能会被投递到不同的进程。连接相关的 PHP 对象数据，无法实现在 onConnect 回调初始化数据，onClose 清理数据
 *  2.onConnect/onReceive/onClose 3 种事件可能会并发执行，可能会带来异常
 *
 * 注意：
 *  1.onConnect/onClose 这 2 个回调发生在 worker 进程内，而不是主进程。
 *  2.UDP 协议下只有 onReceive 事件，没有 onConnect/onClose 事件
 *
 * @link https://wiki.swoole.com/#/server/events?id=onconnect onConnect
 */
class Connect
{
    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var int 连接的文件描述符
     */
    public $fd;

    /**
     * @var int 连接所在的 Reactor 线程 ID
     */
    public $reactorId;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 连接所在的 Reactor 线程 ID
     */
    public function __construct($server, $fd, $reactorId)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }
}