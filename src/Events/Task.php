<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 在 task 进程内被调用。worker 进程可以使用 task 函数向 task_worker 进程投递新的任务。
 * 当前的 Task 进程在调用 onTask 回调函数时会将进程状态切换为忙碌，这时将不再接收新的 Task，当 onTask 函数返回时会将进程状态切换为空闲然后继续接收新的 Task。
 *
 * V4.2.12 起如果开启了 task_enable_coroutine 则回调函数原型是: function (\Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server, \Flysion\Swoolaravel\Swoole\Server|\Flysion\Swoolaravel\Swoole\Http\Server|\Flysion\Swoolaravel\Swoole\WebSocket\Server\Task $task)
 *
 * 返回执行结果到 worker 进程：
 *  1.在 onTask 函数中 return 字符串，表示将此内容返回给 worker 进程。
 *    worker 进程中会触发 onFinish 函数，表示投递的 task 已完成，当然你也可以通过 \Flysion\Swoolaravel\Swoole\Server|\Flysion\Swoolaravel\Swoole\Http\Server|\Flysion\Swoolaravel\Swoole\WebSocket\Server->finish() 来触发 onFinish 函数，而无需再 return
 *  2.return 的变量可以是任意非 null 的 PHP 变量
 *
 * @link https://wiki.swoole.com/#/server/events?id=ontask onTask
 * @link https://wiki.swoole.com/#/server/methods?id=task 任务投递
 * @see \Flysion\Swoolaravel\Swoole\Server|\Flysion\Swoolaravel\Swoole\Http\Server|\Flysion\Swoolaravel\Swoole\WebSocket\Server\Task
 */
class Task
{
    /**
     * swoole 事件名称
     */
    const name = 'task';

    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var int 执行任务的 task 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     */
    public $taskId;

    /**
     * @var int 投递任务的 worker 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     */
    public $srcWorkerId;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     * @param int $taskId 执行任务的 task 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     * @param int $srcWorkerId 投递任务的 worker 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     * @param mixed $data
     */
    public function __construct($server, $taskId, $srcWorkerId, $data)
    {
        $this->server = $server;
        $this->taskId = $taskId;
        $this->srcWorkerId = $srcWorkerId;
        $this->data = $data;
    }
}