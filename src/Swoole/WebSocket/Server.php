<?php

namespace Flysion\Swoolaravel\Swoole\WebSocket;

/**
 * @link https://wiki.swoole.com/#/websocket_server
 * @mixin \Swoole\WebSocket\Server
 */
class Server extends \Swoole\WebSocket\Server
{
    use \Flysion\Swoolaravel\Server;

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