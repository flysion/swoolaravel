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
     * 创建一个 swoole server
     *
     * @param string $host
     * @param int $port
     * @param int $mode
     * @param int $sockType
     * @return \Swoole\Server
     */
    protected static function create($host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP)
    {
        return new \Swoole\Http\Server($host, $port, $mode, $sockType);
    }
}