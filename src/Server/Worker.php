<?php namespace Lee2son\Laravoole\Server;

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
     * @var Repository|null $global 用于用户保存全局变量（如果发生worker进程重启，则全局变量被清空）
     */
    public $global = null;

    /**
     * Worker constructor.
     * @param $workerId worker进程ID（event 进程和 task 进程统称 worker 进程）
     * @param $taskId task进程ID（如果是 event 进程则 $taskId = -1）
     */
    public function __construct($workerId, $taskId = -1)
    {
        $this->workerId = $workerId;
        $this->taskId = $taskId;
        $this->global = new Repository([]);
    }

    /**
     * 是否是 task 进程
     * @return bool
     */
    public function isTask()
    {
        return $this->task_id >= 0;
    }
}