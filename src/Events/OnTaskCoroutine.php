<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onTask()
 */
class OnTaskCoroutine
{
    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @var \Swoole\Server\Task
     */
    public $task;

    /**
     * @param \Swoole\Server
     * @param \Swoole\Server\Task $task
     */
    public function __construct($server, $task)
    {
        $this->server = $server;
        $this->task = $task;
    }
}