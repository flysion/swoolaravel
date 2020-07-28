<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onShutdown()
 */
class OnShutdown
{
    /**
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * @param \Swoole\Server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}