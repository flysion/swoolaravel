<?php

namespace Lee2son\Swoolaravel\Swoole\WebSocket;

/**
 * @link https://wiki.swoole.com/#/websocket_server
 * @mixin \Swoole\WebSocket\Server
 */
class Server extends \Lee2son\Swoolaravel\Swoole\Http\Server
{
    /**
     * @param \Illuminate\Config\Repository $config
     * @return \Swoole\WebSocket\Server
     */
    protected static function createSwooleServer($config)
    {
        return new \Swoole\WebSocket\Server(
            $config->get('host') ?: '0.0.0.0',
            $config->get('port') ?? 0
        );
    }
}