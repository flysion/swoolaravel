<?php

namespace Flysion\Swoolaravel\Swoole\WebSocket;

use phpDocumentor\Reflection\Types\Parent_;

/**
 * @link https://wiki.swoole.com/#/websocket_server
 * @mixin \Swoole\WebSocket\Server
 */
class Server extends \Flysion\Swoolaravel\Swoole\Http\Server
{
    /**
     * @param string $host
     * @param int $port
     */
    public function __construct($host, $port = 0)
    {
        parent::__construct($host, $port);
    }

    /**
     * @return \Swoole\WebSocket\Server
     */
    protected function createSwooleServer()
    {
        return new \Swoole\WebSocket\Server($this->host, $this->port);
    }
}