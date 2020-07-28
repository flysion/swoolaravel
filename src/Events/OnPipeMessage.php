<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onPipeMessage()
 */
class OnPipeMessage
{
    /**
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * @var int 消息来自哪个 Worker 进程
     */
    protected $srcWorkerId;

    /**
     * @var mixed 消息内容，可以是任意 PHP 类型
     */
    protected $message;

    /**
     * @param \Swoole\Server
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