<?php

namespace Flysion\Swoolaravel\Swoole\Process;

/**
 * @link https://wiki.swoole.com/#/process/process?id=process
 */
abstract class Process extends \Swoole\Process
{
    /**
     * @var null|string
     */
    protected $processName = null;

    /**
     * @var array
     */
    protected $callbacks = [];

    /**
     * @param bool $redirect_stdin_and_stdout
     * @param int $pipe_type
     * @param bool $enable_coroutine
     */
    public function __construct($redirect_stdin_and_stdout = false, $pipe_type = SOCK_DGRAM, $enable_coroutine = false)
    {
        parent::__construct(
            function() { $this->main(); },
            $redirect_stdin_and_stdout,
            $pipe_type,
            $enable_coroutine
        );
    }

    /**
     *
     */
    protected function main()
    {
        if(!is_null($this->processName)) {
            $this->name($this->processName);
        }

        // 加载一个新的app替换老的app
        // 这里主要作用是重置框架里的一些东西（清除容器）

        $server = app('server');

        $app = require base_path('/bootstrap/app.php');
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $app->instance('server', $server);
        $app->instance('process', $this);

        $this->handle();
    }

    /**
     * @param string $processName
     * @return static
     */
    public function setProcessName($processName)
    {
        $this->processName = $processName;

        return $this;
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return static
     */
    public function on($name, $callback)
    {
        $this->callbacks['on' . $name] = $callback;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    protected function trigger($name, ...$arguments)
    {
        if(isset($this->callbacks[$name])) {
            return [true, call_user_func($this->callbacks[$name], $this, ...$arguments)];
        }

        return [false, null];
    }

    /**
     * @return void
     */
    abstract public function handle();
}