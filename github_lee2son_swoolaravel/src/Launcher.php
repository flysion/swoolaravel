<?php

namespace Lee2son\Swoolaravel;

abstract class Launcher
{
    /**
     * @param array $config
     * @return \Lee2son\Swoolaravel\Swoole\Server|\Lee2son\Swoolaravel\Swoole\Http\Server|\Lee2son\Swoolaravel\Swoole\WebSocket\Server $server
     */
    abstract protected function createServer($config);

    /**
     * @param array $config
     */
    public function start($config)
    {
        $server = $this->createServer($config);
        return $server->start();
    }
}