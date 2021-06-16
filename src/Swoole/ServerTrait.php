<?php

namespace Flysion\Swoolaravel\Swoole;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ServerTrait
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
    private $events;

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    protected function createEvents()
    {
        return (new \Illuminate\Events\Dispatcher())->setQueueResolver(function () {
            return app()->make(\Illuminate\Contracts\Queue\Factory::class);
        });
    }

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    public function events()
    {
        return $this->events = $this->events ?? $this->createEvents();
    }

    /**
     * @param string $eventName
     * @param callable[]|callable $listeners
     * @throws
     */
    public function on($eventName, $listeners)
    {
        $class = \Flysion\Swoolaravel\events[$eventName];

        parent::on($eventName, function(...$arguments) use($eventName, $class) {
            $event = new $class(...$arguments);

            $result = $this->onBefore($eventName, $event);
            if ($result === false) {
                return;
            }

            $this->events()->dispatch($eventName, is_null($result) ? [$this, $event] : [$this, $event, $result]);

            $this->onAfter($eventName, $event);
        });

        foreach(Arr::wrap($listeners) as $listener)
        {
            $this->events()->listen($eventName, $listener);
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
        $before = 'on' . ucfirst($name) . 'Before';

        if(method_exists($this, $before))
        {
            return $this->{$before}($event);
        }
    }

    /**
     * @param string $name
     * @param \Flysion\Swoolaravel\Events\SwooleEvent $event
     * @return void|false
     * @throws
     */
    final protected function onAfter($name, $event)
    {
        $after = 'on' . ucfirst($name) . 'After';

        if(method_exists($this, $after))
        {
            $this->{$after}($event);
        }
    }

    /**
     * 在触发用户的start事件之前执行
     *
     * @param \Flysion\Swoolaravel\Events\Start $event
     */
    protected function onStartBefore(\Flysion\Swoolaravel\Events\Start $event)
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
    protected function onManagerStartBefore(\Flysion\Swoolaravel\Events\ManagerStart $event)
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
    protected function onWorkerStartBefore(\Flysion\Swoolaravel\Events\WorkerStart $event)
    {
        // 设置工作进程名称

        if($this->processNamePrefix) {
            if ($this->taskworker) {
                \swoole_set_process_name("{$this->processNamePrefix}-taskworker-{$this->worker_pid}-{$event->workerId}");
            } else {
                \swoole_set_process_name("{$this->processNamePrefix}-worker-{$this->worker_pid}-{$event->workerId}");
            }
        }

        // 加载一个新的app替换老的app
        // 这里主要作用是重置框架里的一些东西（清除容器）

        $app = require base_path('/bootstrap/app.php');
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $app->instance('server', $this);
    }

    /**
     * 最大 worker_id
     *
     * @return int
     */
    public function maxWorkerId()
    {
        return max(0, $this->setting['worker_num'] - 1);
    }

    /**
     * @return mixed
     */
    public function minTaskWorkerId()
    {
        return max(0, $this->setting['worker_num']);
    }

    /**
     * @return mixed
     */
    public function maxTaskWorkerId()
    {
        return max(0, $this->setting['worker_num'] + $this->setting['task_worker_num'] - 1);
    }

    /**
     * 工作 worker
     *
     * @return int[]
     */
    public function workers()
    {
        return range(0, $this->maxWorkerId());
    }

    /**
     * task worker
     *
     * @return int[]
     */
    public function taskWorkers()
    {
        return range($this->minTaskWorkerId(), $this->maxTaskWorkerId());
    }

    /**
     * all worker
     *
     * @return int[]
     */
    public function allWorkers()
    {
        return range(0, $this->maxTaskWorkerId());
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
     */
    protected function bootstrap(&$setting)
    {

    }

    /**
     * @param array $setting
     * @return mixed
     * @throws
     */
    public function start($setting = [])
    {
        foreach(get_class_methods($this) as $methodName)
        {
            $str = Str::snake($methodName);

            if(Str::startsWith($str, 'boot_') && Str::endsWith($str, '_strap'))
            {
                $this->{$methodName}($setting);
            }
        }

        $this->bootstrap($setting);

        //

        foreach(\Flysion\Swoolaravel\events as $name => $class)
        {
            $beforeMethod = 'on' . ucfirst($name) . 'Before';
            $afterMethod = 'on'. ucfirst($name) . 'After';

            if(method_exists($this, $beforeMethod) || method_exists($this, $afterMethod))
            {
                $this->on($name, null);
            }
        }

        //

        $this->set(array_merge(
            $this->setting ?? [],
            $this->setting($setting),
            ['task_use_object' => false]
        ));

        //

        putenv('APP_RUNNING_IN_SWOOLE=TRUE');
        app()->instance('server', $this);
        $result = parent::start();
        putenv('APP_RUNNING_IN_SWOOLE=FALSE');

        return $result;
    }
}