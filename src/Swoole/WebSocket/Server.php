<?php

namespace Flysion\Swoolaravel\Swoole\WebSocket;

/**
 * @link https://wiki.swoole.com/#/websocket_server
 * @mixin \Swoole\WebSocket\Server
 */
class Server extends \Flysion\Swoolaravel\Swoole\Http\Server
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
    public static function create($host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP)
    {
        return new \Swoole\WebSocket\Server($host, $port, $mode, $sockType);
    }
}