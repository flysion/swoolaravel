<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onWorkerExit()
 */
class OnWorkerExit
{
    /**
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * @var int
     */
    protected $workerId;

    /**
     * @param \Swoole\Server
     */
    public function __construct($server, $workerId)
    {
        $this->server = $server;
        $this->workerId = $workerId;
    }
}