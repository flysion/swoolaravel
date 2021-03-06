<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 此回调函数在 worker 进程被调用，当 worker 进程投递的任务在 task 进程中完成时， task 进程会通过 Swoole\Server->finish() 方法将任务处理的结果发送给 worker 进程。
 *
 * 注意：
 *  1.task 进程的 onTask 事件中没有调用 finish 方法或者 return 结果，worker 进程不会触发 onFinish
 *  2.执行 onFinish 逻辑的 worker 进程与下发 task 任务的 worker 进程是同一个进程
 *
 * @link https://wiki.swoole.com/#/server/events?id=onfinish onFinish
 */
class Finish
{
    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var int 执行任务的 task 进程 id
     */
    public $taskId;

    /**
     * @var mixed 任务处理的结果内容
     */
    public $data;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     * @param int $taskId 执行任务的 task 进程 id
     * @param mixed $data 任务处理的结果内容
     */
    public function __construct($server, $taskId, $data)
    {
        $this->server = $server;
        $this->taskId = $taskId;
        $this->data = $data;
    }
}