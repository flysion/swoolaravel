<?php namespace Lee2son\Laravoole\Server;

use Swoole\Table as SwooleTable;
use Swoole\WebSocket\Server as SwooleWebsocketServer;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Frame as SwooleWebSocketFrame;
use Illuminate\Http\Request;

class WebSocket extends Http {

    const SWOOLE_SERVER = SwooleWebsocketServer::class;

    /**
     * Server constructor
     */
    public function __construct($host, $port, $settings, $process_mode = SWOOLE_PROCESS, $sock_type = SWOOLE_SOCK_TCP)
    {
        parent::__construct($host, $port, $settings, $process_mode, $sock_type);

        $this->on('Message');
        $this->on('Open');
    }

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数 see https://wiki.swoole.com/wiki/page/401.html
     * @param SwooleWebSocketServer $server
     * @param SwooleHttpRequest $req
     * @return false|[SwooleWebsocketServer $server, Request $request, int $fd, array $client]
     */
    protected function onOpen($server, SwooleHttpRequest $req)
    {
        $request = swoole_request_to_laravel_request($req);

        if(!$this->checkOrigin($request)) {
            $server->close($req->fd);
            return false;
        }

        return [$server, $request, $req->fd, $client ?? null];
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数 see https://wiki.swoole.com/wiki/page/402.html
     * 客户端发送的ping帧不会触发onMessage，底层会自动回复pong包
     * @param SwooleWebSocketServer $server
     * @param SwooleWebSocketFrame $frame
     * @return [SwooleWebSocketServer $server, SwooleWebSocketFrame $frame]
     */
    protected function onMessage($server, SwooleWebSocketFrame $frame)
    {
        return [$server, $frame];
    }

    /**
     * 检查客户端连接源
     * @param \Illuminate\Http\Request $request
     * @return bool 如果返回 false 则连接会被关闭，同时不再触发 onOpen 事件
     */
    protected function checkOrigin(Request $request) : bool
    {
        return true;
    }
}