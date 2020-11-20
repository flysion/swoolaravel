<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 当工作进程收到由 $server->sendMessage() 发送的 unixSocket 消息时会触发 onPipeMessage 事件。worker/task 进程都可能会触发 onPipeMessage 事件
 *
 * @link https://wiki.swoole.com/#/server/events?id=onpipemessage onPipeMessage
 */
class PipeMessage
{
    const SWOOLE_EVENT_NAME = 'pipeMessage';

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