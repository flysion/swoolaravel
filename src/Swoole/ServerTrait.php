<?php

namespace Flysion\Swoolaravel\Swoole;

use Illuminate\Support\Arr;

trait ServerTrait
{
    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * @var array
     */
    protected $swooleEvents = [];

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
     */
    protected function enableEvent($eventName)
    {
        if (isset($this->swooleEvents[$eventName])) return;

        $className = \Flysion\Swoolaravel\events[$eventName];

        parent::on($eventName, function() use($eventName, $className) {
            try {
                $event = new $className(...func_get_args());

                $result = $this->onBefore($eventName, $event);
                if ($result === false) {
                    return;
                }

                $this->events()->dispatch($eventName, is_null($result) ? [$this, $event] : [$this, $event, $result]);

                $this->onAfter($eventName, $event);
            } catch (\Exception $e) {
                report($e);
            }
        });

        $this->swooleEvents[$eventName] = true;
    }

    /**
     * @param string $eventName
     * @param callable[]|callable $listeners
     * @throws
     */
    public function on($eventName, $listeners)
    {
        $this->enableEvent($eventName);

        foreach(Arr::wrap($listeners) as $listener)
        {
            $this->events()->listen($eventName, $listener);
        }
    }

    /**
     * @param string $name
     * @param \Flysion\Swoolaravel\Events\SwooleEvent $event
     * @return false|mixed
     * @throws
     */
    final protected function onBefore($name, $event)
    {
        $method = 'on' . ucfirst($name) . 'Before';

        if(method_exists($this, $method))
        {
            return $this->{$method}($event);
        }
    }

    /**
     * @param string $name
     * @param \Flysion\Swoolaravel\Events\SwooleEvent $event
     * @return void
     * @throws
     */
    final protected function onAfter($name, $event)
    {
        $method = 'on' . ucfirst($name) . 'After';

        if(method_exists($this, $method))
        {
            $this->{$method}($event);
        }
    }

    /**
     * 在触发用户的workerstart事件之前执行
     *
     * @param \Flysion\Swoolaravel\Events\WorkerStart $event
     */
    protected function onWorkerStartBefore(\Flysion\Swoolaravel\Events\WorkerStart $event)
    {
        $app = require base_path('/bootstrap/app.php');
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $app->instance('server', $this);
    }

    /**
     * @param array $setting
     * @return mixed
     * @throws
     */
    public function start($setting = [])
    {
        foreach(\Flysion\Swoolaravel\events as $name => $class)
        {
            $beforeMethod = 'on' . ucfirst($name) . 'Before';
            $afterMethod = 'on'. ucfirst($name) . 'After';

            if(method_exists($this, $beforeMethod) || method_exists($this, $afterMethod))
            {
                $this->enableEvent($name);
            }
        }

        // bootstrap

        if(method_exists($this, 'bootstrap')) {
            $setting = $this->bootstrap($setting);
        }

        foreach(get_class_methods($this) as $method) {
            if (substr($method, 0, 4) === 'boot' && substr($method, -5) === 'Strap') {
                $setting = $this->{$method}($setting);
            }
        }

        // setting

        $this->set(array_merge(
            $setting,
            $this->setting ?? [],
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