<?php

namespace Lee2son\Swoolaravel\Bootstraps;

interface BootstrapInterface
{
    /**
     * @param \Lee2son\Swoolaravel\Swoole\Server|\Lee2son\Swoolaravel\Swoole\Http\Server|\Lee2son\Swoolaravel\Swoole\WebSocket\Server $server
     */
    public function handle($server);
}