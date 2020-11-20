<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 当 WebSocket 客户端与服务器建立连接并完成握手后会回调此函数。
 * 事件函数中可以调用 push 向客户端发送数据或者调用 close 关闭连接
 *
 * @link https://wiki.swoole.com/#/websocket_server?id=onopen onOpen
 * @link https://wiki.swoole.com/#/http_server?id=httprequest \Swoole\Http\Request
 */
class Open
{
    const SWOOLE_EVENT_NAME = 'open';

    /**
     * @var \Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var \Swoole\Http\Request
     */
    public $request;

    /**
     * @param \Swoole\WebSocket\Server $server
     * @param \Swoole\Http\Request $request
     */
    public function __construct($server, $request)
    {
        $this->server = $server;
        $this->request = $request;
    }
}