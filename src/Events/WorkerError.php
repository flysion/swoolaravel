<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 当 Worker/Task 进程发生异常后会在 Manager 进程内回调此函数。
 *
 * 此函数主要用于报警和监控，一旦发现 Worker 进程异常退出，那么很有可能是遇到了致命错误或者进程 CoreDump。通过记录日志或者发送报警的信息来提示开发者进行相应的处理。
 *
 * 常见错误：
 *  1.signal = 11：说明 Worker 进程发生了 segment fault 段错误，可能触发了底层的 BUG，请收集 core dump 信息和 valgrind 内存检测日志，向我们反馈此问题
 *  2.exit_code = 255：说明 Worker 进程发生了 Fatal Error 致命错误，请检查 PHP 的错误日志，找到存在问题的 PHP 代码，进行解决
 *  3.signal = 9：说明 Worker 被系统强行 Kill，请检查是否有人为的 kill -9 操作，检查 dmesg 信息中是否存在 OOM（Out of memory）
 *  4.如果存在 OOM，分配了过大的内存。是否创建了非常大的 \Swoole\Table 内存模块。
 *
 * @link https://wiki.swoole.com/#/server/events?id=onworkererror onWorkerError
 */
class WorkerError
{
    const SWOOLE_EVENT_NAME = 'workerError';

    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var int 异常 worker 进程的 id
     */
    public $workerId;

    /**
     * @var int 异常 worker 进程的 pid
     */
    public $workerPid;
    
    /**
     * @var int 退出的状态码，范围是 0～255
     */
    public $exitCode;

    /**
     * @var int 进程退出的信号
     */
    public $signal;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     * @param int $workerId 异常 worker 进程的 id
     * @param int $workerPid 异常 worker 进程的 pid
     * @param int $exitCode 退出的状态码，范围是 0～255
     * @param int $signal 进程退出的信号
     */
    public function __construct($server, $workerId, $workerPid, $exitCode, $signal)
    {
        $this->server = $server;
        $this->workerId = $workerId;
        $this->workerPid = $workerPid;
        $this->exitCode = $exitCode;
        $this->signal = $signal;
    }
}