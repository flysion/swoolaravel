<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\WebSocket\Server::onMessage()
 */
class OnMessage
{
    /**
     * @var \Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var \Swoole\WebSocket\Frame 是 Swoole\WebSocket\Frame 对象，包含了客户端发来的数据帧信息
     * @link https://wiki.swoole.com/#/websocket_server?id=swoolewebsocketframe \Swoole\WebSocket\Frame
     */
    public $frame;

    /**
     * @param \Swoole\WebSocket\Server $server
     * @param \Swoole\WebSocket\Frame $frame
     */
    public function __construct($server, $frame)
    {
        $this->server = $server;
        $this->frame = $frame;
    }
}