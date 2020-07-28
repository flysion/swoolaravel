<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onFinish()
 */
class OnFinish
{
    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @var int 执行任务的 task 进程 id
     */
    public $taskId;

    /**
     * @var string 任务处理的结果内容
     */
    public $data;

    /**
     * @param \Swoole\Server
     * @param int $taskId
     * @param string $data
     */
    public function __construct($server, $taskId, $data)
    {
        $this->server = $server;
        $this->taskId = $taskId;
        $this->data = $data;
    }
}