<?php

namespace Flysion\Swoolaravel\Swoole;

class Worker
{
    /**
     * @var int
     */
    protected $workerId;

    /**
     * @param int $workerId
     */
    public function __construct($workerId)
    {
        $this->workerId = $workerId;
    }

    /**
     * @link https://wiki.swoole.com/#/server/properties?id=worker_id worker_id的范围
     * @return bool
     */
    public function isTask()
    {
        return $this->workerId >= app('server')->minTaskWorkerId() && $this->workerId <= app('server')->maxTaskWorkerId();
    }

    /**
     * @link https://wiki.swoole.com/#/server/methods?id=getworkerstatus
     *
     * @return bool
     */
    public function isBusy()
    {
        return app('server')->getWorkerStatus($this->workerId) === SWOOLE_WORKER_BUSY;
    }

    /**
     * @link https://wiki.swoole.com/#/server/methods?id=getworkerstatus
     *
     * @return bool
     */
    public function isIdle()
    {
        return app('server')->getWorkerStatus($this->workerId) === SWOOLE_WORKER_IDLE;
    }

    /**
     * @link https://wiki.swoole.com/#/server/methods?id=getworkerstatus
     *
     * @return bool
     */
    public function isExit()
    {
        return app('server')->getWorkerStatus($this->workerId) === SWOOLE_WORKER_EXIT;
    }
}