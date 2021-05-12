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
    public $serverName = 'swoolaravel';

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
        $class = \Flysion\Swoolaravel\events[$name];

        parent::on($name, function(...$arguments) use($name, $class) {
            $event = new $class(...$arguments);

            if($this->onBefore($name, $event) === false) {
                return;
            }

            $this->events->dispatch($name, [$this, $event]);

            $this->onAfter($name, $event);
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
        if($this->serverName)
        {
            \swoole_set_process_name("{$this->serverName}-master-{$this->master_pid}");
        }
    }

    /**
     * 在触发用户的managerstart事件之前执行
     *
     * @param \Flysion\Swoolaravel\Events\ManagerStart $event
     */
    protected function onBeforeManagerStart(\Flysion\Swoolaravel\Events\ManagerStart $event)
    {
        if($this->serverName)
        {
            \swoole_set_process_name("{$this->serverName}-manager-{$this->manager_pid}");
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

        if($this->serverName) {
            if ($this->taskworker) {
                \swoole_set_process_name("{$this->serverName}-taskworker-{$this->worker_pid}-{$event->workerId}");
            } else {
                \swoole_set_process_name("{$this->serverName}-worker-{$this->worker_pid}-{$event->workerId}");
            }
        }
    }

    /**
     * @param string $eventClass
     * @param \Flysion\Swoolaravel\Events\SwooleEvent $event
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

        $this->events->dispatch("{$name}:before", [$this, $event]);
    }

    /**
     * @param string $eventClass
     * @param \Flysion\Swoolaravel\Events\SwooleEvent $event
     * @throws
     */
    final protected function onAfter($name, $event)
    {
        $this->events->dispatch("{$name}:after", [$this, $event]);

        // 内置 after

        $after = 'onAfter' . ucfirst($name);

        if(method_exists($this, $after))
        {
            $this->{$after}($event);
        }
    }

    /**
     * @param array &$setting
     */
    protected function boot(&$setting)
    {

    }

    /**
     * @return mixed
     * @throws
     */
    public function start($setting = [])
    {
        // 注入一个服务实例到应用程序容器里
        app()->instance('server', $this);

        // 留给用户的可编程空间
        // 在服务启动之前做一些初始化工作

        $this->boot($setting);

        // 服务设置
        // 当前设置 > 服务已有设置

        $newSetting = array_merge($this->setting ?? [], $setting, ['task_use_object' => false]);

        if($this->openHttpProtocol) {
            $newSetting['open_http_protocol'] = true;
            $this->on('request', \Flysion\Swoolaravel\Listeners\RequestToLaravel::class);
        }

        $this->set($newSetting);

        // 通过判断是否实现了某些方法从而决定是否要注册某些 swoole 事件

        foreach(\Flysion\Swoolaravel\events as $name => $class)
        {
            $beforeMethod = 'onBefore' . ucfirst($name);
            $afterMethod = 'onAfter'. ucfirst($name);

            if(method_exists($this, $beforeMethod) || method_exists($this, $afterMethod))
            {
                $this->on($name, null);
            }
        }

        // 启动服务
        // 标记应用程序是在 swoole 中执行

        putenv('APP_RUNNING_IN_SWOOLE=TRUE');
        $result = parent::start();
        putenv('APP_RUNNING_IN_SWOOLE=FALSE');

        return $result;
    }
}