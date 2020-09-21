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
    protected $event;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var \Swoole\Server
     */
    protected $swooleServer;

    /**
     * @param array|string $config
     * @param \Illuminate\Events\Dispatcher $event
     */
    public function __construct($config, $event)
    {
        $this->config = new \Illuminate\Config\Repository(is_string($config) ? config($config) : $config);
        $this->event = $event;
    }

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    public function event()
    {
        return $this->event;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function logger()
    {
        if(!$this->logger)
        {
            return $this->logger = \Illuminate\Support\Facades\Log::channel($this->config('log', \Illuminate\Support\Facades\Log::getDefaultDriver()));
        }

        return $this->logger;
    }

    /**
     * @return \Swoole\Server
     */
    public function swooleServer()
    {
        if(!$this->swooleServer)
        {
            $this->swooleServer = $this->createSwooleServer($this->config());
        }

        return $this->swooleServer;
    }

    /**
     * @param null $key
     * @param null $default
     * @return \Illuminate\Config\Repository|mixed
     */
    public function config($key = null, $default = null)
    {
        if(is_null($key))
        {
            return $this->config;
        }

        return $this->config->get($key, $default);
    }

    /**
     * swoole 默认设置，优先级最高，无法被其他设置覆盖
     *
     * @return array
     */
    protected function defaultSetting()
    {
        return [
            'task_enable_coroutine' => false,
            'task_use_object' => false,
        ];
    }

    /**
     * @link https://wiki.swoole.com/#/learn?id=server%e7%9a%84%e4%b8%a4%e7%a7%8d%e8%bf%90%e8%a1%8c%e6%a8%a1%e5%bc%8f%e4%bb%8b%e7%bb%8d 两种运行模式介绍
     * @param \Illuminate\Config\Repository $config
     * @return \Swoole\Server
     */
    protected static function createSwooleServer($config)
    {
        return new \Swoole\Server(
            $config->get('host') ?: '0.0.0.0',
            $config->get('port') ?? 0,
            $config->get('mode') ?? SWOOLE_PROCESS,
            $config->get('sock_type') ?? SWOOLE_SOCK_TCP
        );
    }

    /**
     * 启用 HTTP 请求；必须在 start 之前调用
     */
    public function enableHttpRequest()
    {
        $this->swooleServer()->set(['open_http_protocol' => true]);
    }

    /**
     * 将 http 请求转发到 laravel 框架
     * 必须打开 swooler server 的 open_http_protocol 选项
     */
    protected function httpRequestToLaravel()
    {
        $this->on(\Lee2son\Swoolaravel\Events\Request::class, \Lee2son\Swoolaravel\Listeners\RequestToLaravel::class);
    }

    /**
     *
     * @param string $prefix
     */
    protected function setProcessName($prefix)
    {
        $this->on(Start::class, function($server, Start $event) use($prefix) {
            swoole_set_process_name("{$prefix}master-{$event->server->master_pid}");
        });

        $this->on(ManagerStart::class, function($server, ManagerStart $event) use($prefix) {
            swoole_set_process_name("{$prefix}manager-{$event->server->manager_pid}");
        });

        $this->on(WorkerStart::class, function($server, WorkerStart $event) use($prefix) {
            if($event->server->taskworker) {
                swoole_set_process_name("{$prefix}taskworker-{$event->server->worker_pid}-{$event->workerId}");
            } else {
                swoole_set_process_name("{$prefix}worker-{$event->server->worker_pid}-{$event->workerId}");
            }
        });
    }

    /**
     * 注册事件引导器
     *
     * @param $eventName
     * @param $bootstraps
     */
    public function registerBootstraps($eventName, $bootstraps)
    {
        foreach($bootstraps as $bootstrap)
        {
            app()->singleton($bootstrap);
        }

        app()->tag($bootstraps, "bootstraps.{$eventName}");
    }

    /**
     * 注册事件清理器
     *
     * @param $eventName
     * @param $bootstraps
     */
    public function registerCleaners($eventName, $cleaners)
    {
        foreach($cleaners as $cleaner)
        {
            app()->singleton($cleaner);
        }

        app()->tag($cleaners, "cleaners.{$eventName}");
    }

    /**
     * @param string $event
     * @param callable[] $callbacks
     */
    public function on($event, ...$callbacks)
    {
        if(!$this->event()->hasListeners($event))
        {
            $this->registerSwooleServerEvent($event);
        }

        foreach($callbacks as $callback)
        {
            $this->event->listen($event, $callback);
        }
    }

    /**
     * @param $event
     */
    protected function registerSwooleServerEvent($event)
    {
        $this->swooleServer()->on($event::SWOOLE_EVENT_NAME, function(...$arguments) use($event){
            $eventObject = new $event(...$arguments);

            $this->onBefore($event::SWOOLE_EVENT_NAME, $eventObject);
            $this->event()->dispatch($event, [$this, $eventObject]);
            $this->onAfter($event::SWOOLE_EVENT_NAME, $eventObject);
        });
    }

    /**
     * @param string $eventName
     * @param object $event
     */
    protected function onBefore($eventName, $event)
    {
        // 调用事件引导器

        foreach(app()->tagged("bootstraps.{$eventName}") as $bootstrap)
        {
            $bootstrap->handle($this);
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

        foreach(app()->tagged("cleaners.{$eventName}") as $cleaner)
        {
            $cleaner->handle($this);
        }
    }

    /**
     * @return mixed
     */
    public function start()
    {
        $this->swooleServer()->set(array_merge($this->config('setting', []), $this->defaultSetting()));

        // 设置进程名称前缀

        if($prefix = $this->config('process_name_prefix'))
        {
            $this->setProcessName($prefix);
        }

        // 启用 http request

        $this->config('setting.open_http_protocol') && $this->httpRequestToLaravel();

        // 注册事件引导

        foreach($this->config('bootstraps', []) as $eventName => $bootstraps)
        {
            $this->registerBootstraps($eventName, $bootstraps);
        }

        // 注册事件清理器

        foreach($this->config('cleaners', []) as $eventName => $cleaners)
        {
            $this->registerCleaners($eventName, $cleaners);
        }

        // 调用装载器对环境进行装载

        foreach($this->config('loaders', []) as $loader)
        {
            (new $loader)->handle($this);
        }

        return $this->swooleServer()->start();
    }

    /**
     * 配置设置
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->config()->set($key, $value);
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