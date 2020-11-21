<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 当工作进程收到由 $server->sendMessage() 发送的 unixSocket 消息时会触发 onPipeMessage 事件。worker/task 进程都可能会触发 onPipeMessage 事件
 *
 * @link https://wiki.swoole.com/#/server/events?id=onpipemessage onPipeMessage
 */
class PipeMessage implements SwooleEvent
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
    const name = 'pipeMessage';

    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var int
     */
    public $srcWorkerId;

    /**
     * @var mixed
     */
    public $message;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     * @param int $srcWorkerId
     * @param mixed $message
     */
    public function __construct($server, $srcWorkerId, $message)
    {
        $this->server = $server;
        $this->srcWorkerId = $srcWorkerId;
        $this->message = $message;
    }
}