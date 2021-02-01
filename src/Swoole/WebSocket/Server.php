<?php

namespace Flysion\Swoolaravel\Swoole\WebSocket;

/**
 * @link https://wiki.swoole.com/#/websocket_server
 * @mixin \Swoole\WebSocket\Server
 */
class Server extends \Flysion\Swoolaravel\Swoole\Http\Server
{
    /**
     * @param mixed $payload 透传参数，没有什么用处，开发者自己只有发挥
     * @param string $host
     * @param int $port
     */
    public function __construct($payload, $host, $port = 0)
    {
        parent::__construct($payload, $host, $port);
    }

    /**
     * @return \Swoole\WebSocket\Server
     */
    protected function createSwooleServer()
    {
        return new \Swoole\WebSocket\Server($this->host, $this->port);
    }
}