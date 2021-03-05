<?php

namespace Flysion\Swoolaravel\Swoole\WebSocket;

/**
 * @link https://wiki.swoole.com/#/websocket_server
 * @mixin \Swoole\WebSocket\Server
 */
class Server extends \Flysion\Swoolaravel\Swoole\Http\Server
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
        return new \Swoole\WebSocket\Server($host, $port);
    }
}