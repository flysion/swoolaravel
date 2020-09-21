<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * Worker 进程 Reload 之前触发此事件，在 Manager 进程中回调
 *
 * @link https://wiki.swoole.com/#/server/events?id=onbeforereload
 */
class BeforeReload
{
    const SWOOLE_EVENT_NAME = 'beforeReload';

    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}