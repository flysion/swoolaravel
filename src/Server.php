<?php

namespace Flysion\Swoolaravel;

use Illuminate\Support\Arr;

trait Server
{
    /**
     * 进程名称前缀
     *
     * @var string
     */
    public $processNamePrefix = 'swoolaravel';

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    public $events;

    /**
     * @param string $name
     * @param callable[]|callable $listeners
     * @throws
     */
    public function on($name, $listeners)
    {
        if(strpos($name, ':') > 0) {
            list($eventName, $_) = explode(':', $name, 2);
        } else {
            $eventName = $name;
        }

        $class = \Flysion\Swoolaravel\events[$eventName];

        parent::on($eventName, function(...$arguments) use($eventName, $class) {
            $event = new $class(...$arguments);

            try {
                if ($this->onBefore($eventName, $event) === false) {
                    return;
                }

                $this->events->dispatch($eventName, [$this, $event]);

                $this->onAfter($eventName, $event);
            } catch (\Exception $e) {
                report($e);
            }
        });

        foreach(Arr::wrap($listeners) as $listener)
        {
            $this->events->listen($name, $listener);
        }
    }

    /**
     * 在触发用户的start事件之前执行
     *
     * @param \Flysion\Swoolaravel\Events\Start $event
     */
    protected function onBeforeStart(\Flysion\Swoolaravel\Events\Start $event)
    {
        if($this->processNamePrefix)
        {
            \swoole_set_process_name("{$this->processNamePrefix}-master-{$this->master_pid}");
        }
    }

    /**
     * 在触发用户的managerstart事件之前执行
     *
     * @param \Flysion\Swoolaravel\Events\ManagerStart $event
     */
    protected function onBeforeManagerStart(\Flysion\Swoolaravel\Events\ManagerStart $event)
    {
        if($this->processNamePrefix)
        {
            \swoole_set_process_name("{$this->processNamePrefix}-manager-{$this->manager_pid}");
        }
    }

    /**
     * 在触发用户的workerstart事件之前执行
     *
     * @param \Flysion\Swoolaravel\Events\WorkerStart $event
     */
    protected function onBeforeWorkerStart(\Flysion\Swoolaravel\Events\WorkerStart $event)
    {
        // 加载一个新的app替换老的app
        // 这里主要作用是实现代码reload
        // 这将影响 app() 返回的实例

        $app = require base_path('/bootstrap/app.php');

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        \Illuminate\Foundation\Application::setInstance($app);

        $app->instance('server', $this);

        // 设置工作进程名称

        if($this->processNamePrefix) {
            if ($this->taskworker) {
                \swoole_set_process_name("{$this->processNamePrefix}-taskworker-{$this->worker_pid}-{$event->workerId}");
            } else {
                \swoole_set_process_name("{$this->processNamePrefix}-worker-{$this->worker_pid}-{$event->workerId}");
            }
        }
    }

    /**
     * @param string $name
     * @param \Flysion\Swoolaravel\Events\SwooleEvent $event
     * @return void|false
     * @throws
     */
    final protected function onBefore($name, $event)
    {
        $before = 'onBefore' . ucfirst($name);

        if(method_exists($this, $before))
        {
            if($this->{$before}($event) === false) {
                return false;
            }
        }

        //

        $this->events->dispatch("{$name}:before", [$this, $event]);
    }

    /**
     * @param mixed $message
     * @param int $workerId
     * @return bool
     */
    public function sendMessageToTaskWorker($message, $workerId)
    {
        if($workerId >= 0) {
            return parent::sendMessage($message, $workerId);
        }

        $workers = $this->taskWorkers();
        shuffle($workers);

        foreach($workers as $workerId)
        {
            if($this->getWorkerStatus() !== SWOOLE_WORKER_IDLE) continue;
        }

        return $this->sendMessageToTaskWorker($message, $workerId);
    }

    /**
     * 工作进程
     *
     * @return int[]
     */
    public function workers()
    {
        return range(0, max(0, @$this->setting['work_num'] - 1));
    }

    /**
     * task worker 进程
     *
     * @return int[]
     */
    public function taskWorkers()
    {
        return range(
            max(0, @$this->setting['work_num'] - 1),
            max(0, @$this->setting['worker_num'] + @$this->setting['task_worker_num'] - 1)
        );
    }

    /**
     * 所有 worker 进程
     *
     * @return int[]
     */
    public function allWorkers()
    {
        return range(0, max(0, @$this->setting['worker_num'] + @$this->setting['task_worker_num'] - 1));
    }

    /**
     * @param string $name
     * @param \Flysion\Swoolaravel\Events\SwooleEvent $event
     * @return void|false
     * @throws
     */
    final protected function onAfter($name, $event)
    {
        $after = 'onAfter' . ucfirst($name);

        if(method_exists($this, $after))
        {
            $this->{$after}($event);
        }

        //

        $this->events->dispatch("{$name}:after", [$this, $event]);
    }

    /**
     * 获取服务配置
     *
     * @param array $setting start 方法传过来的服务配置
     * @return array
     */
    protected function setting($setting)
    {
        return $setting;
    }

    /**
     * 启动引导程序
     * 在 start 前执行
     *
     * @param array $setting
     * @param mixed[] $arguments
     */
    protected function boot(&$setting, ...$arguments)
    {

    }

    /**
     * @param array $setting
     * @param mixed[] $arguments
     * @return mixed
     * @throws
     */
    public function start($setting = [], ...$arguments)
    {
        $this->boot($setting, ...$arguments);

        //

        $setting = array_merge(
            $this->setting ?? [],
            $this->setting($setting),
            ['task_use_object' => false]
        );

        if($this->openHttpProtocol) {
            $setting['open_http_protocol'] = true;
            $this->on('request', \Flysion\Swoolaravel\Listeners\RequestToLaravel::class);
        }

        $this->set($setting);

        //

        foreach(\Flysion\Swoolaravel\events as $name => $class)
        {
            $beforeMethod = 'onBefore' . ucfirst($name);
            $afterMethod = 'onAfter'. ucfirst($name);

            if(method_exists($this, $beforeMethod) || method_exists($this, $afterMethod))
            {
                $this->on($name, null);
            }
        }

        //

        putenv('APP_RUNNING_IN_SWOOLE=TRUE');

        app()->instance('server', $this);
        $result = parent::start();

        putenv('APP_RUNNING_IN_SWOOLE=FALSE');

        return $result;
    }
}