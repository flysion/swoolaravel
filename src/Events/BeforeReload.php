<?php

namespace Flysion\Swoolaravel\Events;

/**
 * Worker 进程 Reload 之前触发此事件，在 Manager 进程中回调
 *
 * @link https://wiki.swoole.com/#/server/events?id=onbeforereload
 */
class BeforeReload implements SwooleEvent
{
    /**
     * 事件触发之前
     */
    const before = self::class . ':before';

    /**
     * 事件触发之后
     */
    const after = self::class . ':after';

    /**
     * swoole 事件名称
     */
    const name = 'beforeReload';

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