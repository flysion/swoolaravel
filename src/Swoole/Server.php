<?php

namespace Lee2son\Swoolaravel\Swoole;

use Lee2son\Swoolaravel\Events\ManagerStart;
use Lee2son\Swoolaravel\Events\Start;
use Lee2son\Swoolaravel\Events\WorkerStart;

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
abstract class Server
{
    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * @var \Swoole\Server
     */
    protected $swooleServer;

    /**
     * 在启动服务之前要执行的代码
     *
     * @var array
     */
    protected $loaders = [];

    /**
     * 在事件触发之前要执行的代码
     *
     * @var array
     */
    protected $bootstraps = [];

    /**
     * 在事件触发之后要执行的代码
     *
     * @var array
     */
    protected $cleaners = [];

    /**
     * @param \Illuminate\Events\Dispatcher|null $events
     * @param string $host
     * @param int $port
     * @param int $mode
     * @param int $sockType
     */
    public function __construct($events = null, $host = '0.0.0.0')
    {
        $this->events = $events ?? app('events');
    }

    /**
     * @return \Swoole\Server
     */
    abstract public function createSwooleServer();

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * @return \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public function swooleServer()
    {
        if(!$this->swooleServer) {
            $this->swooleServer = $this->createSwooleServer();
        }

        return $this->swooleServer;
    }

    /**
     * 将 http 请求转发到 laravel 框架
     * 必须打开 swooler server 的 open_http_protocol 选项
     */
    public function enableHttpRequestToLaravel()
    {
        $this->on(\Lee2son\Swoolaravel\Events\Request::class, \Lee2son\Swoolaravel\Listeners\RequestToLaravel::class);
    }

    /**
     *
     * @param string $prefix
     */
    public function setProcessName($prefix)
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
     * 注册事件引导器；在事件触发之前执行引导程序
     *
     * @param string $eventName
     * @param array $bootstraps
     */
    public function registerBootstraps($eventName, $bootstraps)
    {
        $this->bootstraps[$eventName] = array_merge($this->bootstraps[$eventName] ?? [], $bootstraps);
    }

    /**
     * 注册事件清理器；在事件触发之后运行清理程序
     *
     * @param string $eventName
     * @param array $bootstraps
     */
    public function registerCleaners($eventName, $cleaners)
    {
        $this->cleaners[$eventName] = array_merge($this->cleaners[$eventName] ?? [], $cleaners);
    }

    /**
     * 注册服务加载器；在服务运行之前执行资源加载
     *
     * @param array $loaders
     */
    public function registerLoaders($loaders)
    {
        $this->loaders = array_merge($this->loaders ?? [], $loaders);
    }

    /**
     * @param string $eventClass
     * @param callable[] $callbacks
     */
    public function on($eventClass, ...$callbacks)
    {
        if(!$this->events()->hasListeners($eventClass))
        {
            $this->registerSwooleServerEvent($eventClass);
        }

        foreach($callbacks as $callback)
        {
            $this->events()->listen($eventClass, $callback);
        }
    }

    /**
     * @param string $eventClass
     */
    protected function registerSwooleServerEvent($eventClass)
    {
        $this->swooleServer()->on($eventClass::SWOOLE_EVENT_NAME, function(...$arguments) use($eventClass){
            $event = new $eventClass(...$arguments);

            $this->onBefore($eventClass::SWOOLE_EVENT_NAME, $event);
            $this->events()->dispatch($eventClass, [$this, $event]);
            $this->onAfter($eventClass::SWOOLE_EVENT_NAME, $event);
        });
    }

    /**
     * @param string $eventName
     * @param object $event
     * @throws
     */
    protected function onBefore($eventName, $event)
    {
        // 调用事件引导器

        foreach($this->bootstraps[$eventName] ?? [] as $bootstrap)
        {
            \Lee2son\Swoolaravel\call($bootstrap, [$this, $event], 'handle');
        }

        // 调用内置 before

        $before = \Illuminate\Support\Str::camel("on_before_{$eventName}");

        if(method_exists($this, $before))
        {
            $this->$before($event);
        }
    }

    /**
     * @param string $eventName
     * @param object $event
     * @throws
     */
    protected function onAfter($eventName, $event)
    {
        // 调用内置 after

        $after = \Illuminate\Support\Str::camel("on_after_{$eventName}");

        if(method_exists($this, $after))
        {
            $this->$after($event);
        }

        // 调用事件清理器

        foreach($this->cleaners[$eventName] ?? [] as $cleaner)
        {
            \Lee2son\Swoolaravel\call($cleaner, [$this, $event], 'handle');
        }
    }

    /**
     * @return mixed
     * @throws
     */
    public function start()
    {
        foreach($this->loaders as $loader)
        {
            \Lee2son\Swoolaravel\call($loader, [$this], 'handle');
        }

        return $this->swooleServer()->start();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->swooleServer()->$name(...$arguments);
    }
}