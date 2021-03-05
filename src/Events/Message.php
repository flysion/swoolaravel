<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 当服务器收到来自客户端的数据帧时会回调此函数。
 * 客户端发送的 ping 帧不会触发 onMessage，底层会自动回复 pong 包
 * $frame->data 如果是文本类型，编码格式必然是 UTF-8，这是 WebSocket 协议规定的
 *
 * @link https://wiki.swoole.com/#/websocket_server?id=onmessage onMessage
 * @link https://wiki.swoole.com/#/websocket_server?id=swoolewebsocketframe \Swoole\WebSocket\Frame
 */
class Message
{
    /**
     * swoole 事件名称
     */
    const name = 'message';

    /**
     * @var \Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var \Swoole\WebSocket\Frame
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