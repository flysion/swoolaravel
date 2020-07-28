<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\WebSocket\Server::onOpen()
 */
class OnOpen
{
    /**
     * @var \Swoole\WebSocket\Server
     */
    protected $server;

    /**
     * @var \Swoole\Http\Request 是一个 HTTP 请求对象，包含了客户端发来的握手请求信息
     * @link https://wiki.swoole.com/#/http_server?id=httprequest \Swoole\Http\Request
     */
    protected $request;

    /**
     * @param \Swoole\WebSocket\Server $server
     * @param \Swoole\Http\Request $request 是一个 HTTP 请求对象，包含了客户端发来的握手请求信息
     */
    public function __construct($server, $request)
    {
        $this->server = $server;
        $this->request = $request;
    }
}