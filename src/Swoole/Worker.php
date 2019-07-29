<?php namespace Lee2son\Swoolaravel\Swoole;

use Illuminate\Config\Repository;

class Worker {
    /**
     * @var int $workerId
     */
    public $workerId;

    /**
     * @var int $taskId
     */
    public $taskId;

    /**
     * @var Repository|null 用于用户保存全局变量（如果发生 worker 进程重启，则全局变量被清空）
     */
    public $globalData = null;

    /**
     * Worker constructor.
     * @param $workerId worker进程ID（event 进程和 task 进程统称 worker 进程）
     * @param $taskId task 进程 ID（如果是 event 进程则 $taskId = -1）
     */
    public function __construct($workerId, $taskId = -1)
    {
        $this->workerId = $workerId;
        $this->taskId = $taskId;
        $this->globalData = new Repository([]);
    }

    /**
     * 是否是 task 进程
     * @return bool
     */
    public function isTask()
    {
        return $this->taskId >= 0;
    }

    public function get($key, $default = null) {
        return $this->globalData->get($key, $default);
    }

    public function set($key, $value = null) {
        $this->globalData->set($key, $value);
    }
}