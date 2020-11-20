<?php

namespace Flysion\Swoolaravel;

abstract class Launcher
{
    /**
     * @param array $config
     * @return \Flysion\Swoolaravel\Swoole\Server|\Flysion\Swoolaravel\Swoole\Http\Server|\Flysion\Swoolaravel\Swoole\WebSocket\Server $server
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