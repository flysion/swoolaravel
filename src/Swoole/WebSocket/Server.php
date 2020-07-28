<?php

namespace Lee2son\Swoolaravel\Swoole\WebSocket;

use Lee2son\Swoolaravel\Events\OnHandShake;
use Lee2son\Swoolaravel\Events\OnMessage;
use Lee2son\Swoolaravel\Events\OnOpen;

/**
 * @link https://wiki.swoole.com/#/websocket_server
 */
trait Server
{
    use \Lee2son\Swoolaravel\Swoole\Http\Server;

    /**
     * 当 WebSocket 客户端与服务器建立连接并完成握手后会回调此函数。
     * 事件函数中可以调用 push 向客户端发送数据或者调用 close 关闭连接
     *
     * @link https://wiki.swoole.com/#/websocket_server?id=onopen onOpen
     * @param \Swoole\WebSocket\Server $server
     * @param \Swoole\Http\Request $request
     */
    public function onOpen($server, \Swoole\Http\Request $request)
    {
        $this->event->dispatch('swoole.open', [$server, $request]);
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数。
     * 客户端发送的 ping 帧不会触发 onMessage，底层会自动回复 pong 包
     * $frame->data 如果是文本类型，编码格式必然是 UTF-8，这是 WebSocket 协议规定的
     *
     * @link https://wiki.swoole.com/#/websocket_server?id=onmessage onMessage
     * @link https://wiki.swoole.com/#/websocket_server?id=swoolewebsocketframe \Swoole\WebSocket\Frame
     * @see \Swoole\WebSocket\Frame
     * @param \Swoole\WebSocket\Server $server
     * @param \Swoole\WebSocket\Frame $frame
     */
    public function onMessage($server, $frame)
    {
        $this->event->dispatch('swoole.message', [$server, $frame]);
    }

    /**
     * WebSocket 建立连接后进行握手。WebSocket 服务器会自动进行 handshake 握手的过程，如果用户希望自己进行握手处理，可以设置 onHandShake 事件回调函数。
     * 设置 onHandShake 回调函数后不会再触发 onOpen 事件，需要应用代码自行处理
     * onHandShake 中必须调用 response->status() 设置状态码为 101 并调用 response->end() 响应，否则会握手失败.
     * 内置的握手协议为 Sec-WebSocket-Version: 13，低版本浏览器需要自行实现握手
     * 可以使用 server->defer 调用 onOpen 逻辑
     *
     * @link https://wiki.swoole.com/#/websocket_server?id=onhandshake onHandShake
     * @link https://wiki.swoole.com/#/http_server?id=httprequest \Swoole\Http\Request
     * @link https://wiki.swoole.com/#/http_server?id=httpresponse \Swoole\Http\Response
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onHandShake($request, $response)
    {
        $this->event->dispatch('swoole.handShake', [$request, $response]);
    }
}