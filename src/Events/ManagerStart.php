<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 当管理进程启动时触发此事件
 * 在这个回调函数中可以修改管理进程的名称。
 * 在 4.2.12 以前的版本中 manager 进程中不能添加定时器，不能投递 task 任务、不能用协程。
 * 在 4.2.12 或更高版本中 manager 进程可以使用基于信号实现的同步模式定时器
 * manager 进程中可以调用 sendMessage 接口向其他工作进程发送消息
 *
 * 启动顺序：
 *  1.Task 和 Worker 进程已创建
 *  2.Master 进程状态不明，因为 Manager 与 Master 是并行的，onManagerStart 回调发生是不能确定 Master 进程是否已就绪
 *
 * BASE 模式：
 *  1.在 SWOOLE_BASE 模式下，如果设置了 worker_num、max_request、task_worker_num 参数，底层将创建 manager 进程来管理工作进程。因此会触发 onManagerStart 和 onManagerStop 事件回调。
 *
 * @link https://wiki.swoole.com/#/server/events?id=onmanagerstart onManagerStart
 */
class ManagerStart
{
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