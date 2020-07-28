<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onBeforeReload()
 */
class OnBeforeReload
{
    /**
     * @var \Swoole\Server
     */
    public $server;
    /**
     * @param \Swoole\Server $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}