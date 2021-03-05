<?php

namespace Flysion\Swoolaravel\Swoole;

use Flysion\Swoolaravel\Events\ManagerStart;
use Flysion\Swoolaravel\Events\Start;
use Flysion\Swoolaravel\Events\WorkerStart;

/**
 * 事件执行顺序：
 *  1.所有事件回调均在 $server->start 后发生
 *  2.服务器关闭程序终止时最后一次事件是 onShutdown
 *  3.服务器启动成功后，onStart/onManagerStart/onWorkerStart 会在不同的进程内并发执行
 *  4.onReceive/onConnect/onClose 在 Worker 进程中触发
 *  5.Worker/Task 进程启动 / 结束时会分别调用一次 onWorkerStart/onWorkerStop
 *  6.onTask 事件仅在 task 进程中发生
 *  7.onFinish 事件仅在 worker 进程中发生
 *  8.onStart/onManagerStart/onWorkerStart 3 个事件的执行顺序是不确定的
 *
 * @link https://wiki.swoole.com/#/server/tcp_init
 * @link https://wiki.swoole.com/#/server/events?id=%e4%ba%8b%e4%bb%b6%e6%89%a7%e8%a1%8c%e9%a1%ba%e5%ba%8f 事件执行顺序
 * @mixin \Swoole\Server
 */
class Server
{
    /**
     * @var \Illuminate\Events\Dispatcher
     */
    public $events;

    /**
     * @var \Swoole\Server
     */
    public $swooleServer;

    /**
     * @param \Illuminate\Events\Dispatcher|null $events
     * @param string $host
     * @param int $port
     * @param int $mode
     * @param int $sockType
     */
    public function __construct($events = null, $host, $port = 0, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP)
    {
        $this->events = $events ?? $this->createEvents();
        $this->swooleServer = $this->createSwooleServer($host, $port, $mode, $sockType);
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $mode
     * @param int $sockType
     * @return \Swoole\Server
     */
    protected function createSwooleServer($host, $port, $mode, $sockType)
    {
        return new \Swoole\Server($host, $port, $mode, $sockType);
    }

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    protected function createEvents()
    {
        return app('events');
    }

    /**
     * 将 http 请求转发到 laravel 框架
     * 必须打开 swooler server 的 open_http_protocol 选项
     */
    public function enableHttpRequestToLaravel()
    {
        $this->swooleServer->set(['open_http_protocol' => true]);
        $this->on(\Flysion\Swoolaravel\Events\Request::class, \Flysion\Swoolaravel\Listeners\RequestToLaravel::class);
    }

    /**
     * 设置进程名称
     * 仅仅是注册一个监听进程启动事件，在事件中设置进程名称
     *
     * @param string $prefix
     */
    public function setProcessNamePrefix($prefix)
    {
        $this->on(Start::class, function($server, Start $event) use($prefix) {
            \swoole_set_process_name("{$prefix}master-{$event->server->master_pid}");
        });

        $this->on(ManagerStart::class, function($server, ManagerStart $event) use($prefix) {
            \swoole_set_process_name("{$prefix}manager-{$event->server->manager_pid}");
        });

        $this->on(WorkerStart::class, function($server, WorkerStart $event) use($prefix) {
            if($event->server->taskworker) {
                \swoole_set_process_name("{$prefix}taskworker-{$event->server->worker_pid}-{$event->workerId}");
            } else {
                \swoole_set_process_name("{$prefix}worker-{$event->server->worker_pid}-{$event->workerId}");
            }
        });
    }

    /**
     * @param string $name
     * @param  \Closure[]|string[] $listeners
     * @throws
     */
    public function on($name, ...$listeners)
    {
        $ref = new \ReflectionClass($name);
        if(!$this->events->hasListeners($name))
        {
            $this->registerSwooleServerEvent($name);
        }

        foreach($listeners as $listener)
        {
            $this->events->listen($name, $listener);
        }
    }

    /**
     * @param string $class
     */
    protected function registerSwooleServerEvent($class)
    {
        $this->swooleServer->on($class::name, function(...$arguments) use($class) {
            $event = new $class(...$arguments);

            $result = $this->onBefore($class, $event);

            if($result === false) {
                return;
            } elseif($result !== null) {
                $this->events->dispatch($class, [$this, $event, $result]);
            } else {
                $this->events->dispatch($class, [$this, $event]);
            }

            $this->onAfter($class, $event);
        });
    }

    /**
     * @param WorkerStart $event
     */
    final protected function onBeforeWorkerStart(\Flysion\Swoolaravel\Events\WorkerStart $event)
    {
        $app = require base_path('/bootstrap/app.php');

        $consoleKernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $consoleKernel->bootstrap();

        \Illuminate\Container\Container::setInstance($app);

        $app->instance('server', $this);
    }

    /**
     * @param string $eventClass
     * @param \Flysion\Swoolaravel\Events\SwooleEvent $event
     * @return false|null|mixed
     * @throws
     */
    final protected function onBefore($class, $event)
    {
        $result = null;

        $before = \Illuminate\Support\Str::camel('on_before_' . $class::name);

        if(method_exists($this, $before))
        {
            $result = $this->{$before}($event);
            if($result === false) {
                return false;
            }
        }

        return $result;
    }

    /**
     * @param string $eventClass
     * @param \Flysion\Swoolaravel\Events\SwooleEvent $event
     * @throws
     */
    final protected function onAfter($class, $event)
    {
        // 内置 after

        $after = \Illuminate\Support\Str::camel('on_after_' . $class::name);

        if(method_exists($this, $after))
        {
            $this->{$after}($event);
        }
    }

    /**
     * @return mixed
     * @throws
     */
    public function start($setting = [])
    {
        putenv('APP_RUNNING_IN_SWOOLE=TRUE');

        app()->instance('server', $this);

        if(method_exists($this, 'onReady')) {
            $this->onReady();
        }

        $this->swooleServer->set(array_merge($setting, $this->swooleServer->setting ?? [], ['task_use_object' => false]));
        $result = $this->swooleServer->start();

        putenv('APP_RUNNING_IN_SWOOLE=FALSE');

        return $result;
    }

    /**
     * proxy to swoole server.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->swooleServer->$name(...$arguments);
    }

    /**
     * proxy to swoole server.
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->swooleServer->{$name};
    }
}