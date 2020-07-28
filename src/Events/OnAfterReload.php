<?php

namespace Lee2son\Swoolaravel\Events;

use Lee2son\Swoolaravel\Swoole\WebSocket\Server;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onAfterReload()
 */
class OnAfterReload
{
    /**
     * @var \Swoole\Server
     */
    protected $server;
    /**
     * @param \Swoole\Server $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}