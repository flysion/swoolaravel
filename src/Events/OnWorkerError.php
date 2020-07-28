<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onWorkerError()
 */
class OnWorkerError
{
    /**
     * @var \Swoole\Server
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
     * @param \Swoole\Server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
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