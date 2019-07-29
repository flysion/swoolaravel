<?php namespace Lee2son\Swoolaravel\Swoole\WebSocket;

use Illuminate\Http\Request;
use Lee2son\Swoolaravel\Swoole\_Server;
use Swoole\WebSocket\Frame as SwooleWebSocketFrame;
use Swoole\Http\Request as SwooleHttpRequest;

class Server extends \Swoole\WebSocket\Server
{
    use _Server, OnRequest;

    public function __construct($host, $port = null, $mode = null, $sock_type = null)
    {
        parent::__construct($host, $port, $mode, $sock_type);

        $this->on('WorkerStart', null);
//        $this->on('Request', null);
        $this->on('Open', null);
        $this->on('Message', null);
    }

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数 see https://wiki.swoole.com/wiki/page/401.html
     * @param $server
     * @param SwooleHttpRequest $req
     * @return false|[$server, $request, $fd]
     */
    protected function onOpen($server, SwooleHttpRequest $req)
    {
        $request = swoole_http_request_to_laravel_http_request($req);

        if(!$this->checkOrigin($request)) {
            $server->close($req->fd);
            return false;
        }

        return [$server, $request, $req->fd];
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数 see https://wiki.swoole.com/wiki/page/402.html
     * 客户端发送的ping帧不会触发onMessage，底层会自动回复pong包
     * @param $server
     * @param Frame $frame
     * @return [$server, SwooleWebSocketFrame $frame]
     */
    protected function onMessage($server, SwooleWebSocketFrame $frame)
    {
        return true;
    }

    /**
     * 检查用户连接源
     * @param Request $request
     * @return bool 如果返回 false 则连接会被断开
     */
    protected function checkOrigin(Request $request) : bool
    {
        return true;
    }
}