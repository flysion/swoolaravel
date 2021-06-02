<?php

namespace Flysion\Swoolaravel\Events;

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
 */
class HandShake implements SwooleEvent
{
    /**
     * @var \Swoole\Http\Request
     */
    public $request;

    /**
     * @var \Swoole\Http\Request
     */
    public $response;

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}