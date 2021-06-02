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
class Server extends \Swoole\Http\Server
{
    use \Flysion\Swoolaravel\Swoole\ServerTrait, \Flysion\Swoolaravel\Swoole\EnableHttp;

    /**
     * @param \Illuminate\Events\Dispatcher|null $events
     * @param string $host
     * @param int $port
     * @param int $mode
     * @param int $sockType
     */
    public function __construct($events = null, $host = '0.0.0.0', $port = 0)
    {
        parent::__construct($host, $port);
        $this->events = $events ?? $this->createEvents();
    }

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    protected function createEvents()
    {
        return app('events');
    }
}