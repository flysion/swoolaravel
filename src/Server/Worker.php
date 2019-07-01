<?php namespace Lee2son\Laravoole\Server;

class Worker {
    /**
     * @var int
     */
    public $worker_id;

    /**
     * @var int
     */
    public $task_id;

    /**
     * worker 进程是否是 task 进程
     * @return bool
     */
    public function isTask()
    {
        return $this->task_id >= 0;
    }
}