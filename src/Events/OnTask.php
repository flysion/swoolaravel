<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onTask()
 */
class OnTask
{
    /**
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * @var int 执行任务的 task 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     */
    protected $taskId;

    /**
     * @var int 投递任务的 worker 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     */
    protected $srcWorkerId;

    /**
     * @var string 任务的数据内容
     */
    protected $data;

    /**
     * @param \Swoole\Server
     * @param int $taskId
     * @param int $srcWorkerId
     * @param string $data
     */
    public function __construct($server, $taskId, $srcWorkerId, $data)
    {
        $this->server = $server;
        $this->taskId = $taskId;
        $this->srcWorkerId = $srcWorkerId;
        $this->data = $data;
    }
}