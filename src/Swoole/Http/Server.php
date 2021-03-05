<?php

namespace Flysion\Swoolaravel\Swoole\Http;

/**
 * 注意：
 *  1.Http\Server 对 HTTP 协议的支持并不完整，一定要作为应用服务器处理动态请求。并且在前端增加 Nginx 作为代理
 *  2.不接受 onConnect/onReceive 回调设置
 *  3.额外接受 1 种新的事件类型 onRequest
 *
 * @link https://wiki.swoole.com/#/http_server
 * @mixin \Swoole\Http\Server
 */
class Server extends \Flysion\Swoolaravel\Swoole\Server
{
    /**
     * @param \Illuminate\Events\Dispatcher|null $events
     * @param string $host
     * @param int $port
     */
    public function __construct($events, $host, $port = 0)
    {
        parent::__construct($events, $host, $port);
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $mode
     * @param int $sockType
     * @return \Swoole\Server
     */
    protected function createSwooleServer($host, $port, $mode, $sockType)
    {
        return new \Swoole\Http\Server($host, $port);
    }
}