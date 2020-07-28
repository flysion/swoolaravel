<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onStart()
 */
class OnStart
{
    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @param \Swoole\Server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}