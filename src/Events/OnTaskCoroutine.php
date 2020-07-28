<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onTaskCoroutine()
 */
class OnTaskCoroutine
{
    /**
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * @var \Swoole\Server\Task
     */
    protected $task;

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