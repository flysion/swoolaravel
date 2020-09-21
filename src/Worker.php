<?php namespace Lee2son\Swoolaravel;

use Illuminate\Config\Repository;

class Worker
{
    /**
     * @var int $workerId
     */
    protected $workerId;

    /**
     * @var Repository|null 用于用户保存全局变量（如果发生 worker 进程重启，则全局变量被清空）
     */
    protected $global = null;

    /**
     * @param int $workerId worker进程ID（event 进程和 task 进程统称 worker 进程）
     */
    public function __construct($workerId)
    {
        $this->workerId = $workerId;
        $this->global = new Repository([]);
    }

    public function get($key, $default = null)
    {
        return $this->global->get($key, $default);
    }

    public function set($key, $value = null)
    {
        $this->global->set($key, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
}