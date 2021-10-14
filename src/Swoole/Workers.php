<?php

namespace Flysion\Swoolaravel\Swoole;

class Workers
{
    /**
     * @return int
     */
    public static function workerNum()
    {
        return intval(app('server')->setting['worker_num'] ?? 0);
    }

    /**
     * @return int
     */
    public static function taskWorkerNum()
    {
        return intval(app('server')->setting['task_worker_num'] ?? 0);
    }

    /**
     * @return int
     */
    public static function num()
    {
        return static::workerNum() + static::taskWorkerNum();
    }

    /**
     * @return int|null
     */
    public static function first()
    {
        return static::num() > 0 ? 0 : null;
    }

    /**
     * @return int|null
     */
    public static function end()
    {
        return static::num() > 0 ? static::num() - 1 : null;
    }

    /**
     * @return int|null
     */
    public static function firstWorker()
    {
        return static::workerNum() > 0 ? 0 : null;
    }

    /**
     * @return int|null
     */
    public static function endWorker()
    {
        return static::workerNum() > 0 ? static::workerNum() - 1 : null;
    }

    /**
     * @return int|null
     */
    public static function firstTaskWorker()
    {
        return static::taskWorkerNum() > 0 ? static::workerNum() : null;
    }

    /**
     * @return int|null
     */
    public static function endTaskWorker()
    {
        return static::taskWorkerNum() > 0 ? static::num() - 1 : null;
    }

    /**
     * @return int[]
     */
    public static function all()
    {
        return static::num() > 0 ? range(0, static::num() - 1) : [];
    }

    /**
     * @return int[]
     */
    public static function workers()
    {
        return static::workerNum() > 0 ? range(0, static::workerNum() - 1) : [];
    }

    /**
     * @return int[]
     */
    public static function taskWorkers()
    {
        return static::taskWorkerNum() > 0 ? range(static::workerNum(), static::taskWorkerNum()) : [];
    }
}