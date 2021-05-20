<?php

namespace Flysion\Swoolaravel\Swoole\Process;
/**
 * 创建一个 swoole 进程，用于调用 laravel 命令
 */
class Artisan extends Process
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var null|\Symfony\Component\Console\Output\OutputInterface
     */
    protected $outputBuffer;

    /**
     * 在启动进程前调用
     *
     * @var callable
     */
    protected $onStart;

    /**
     * @param string $command
     * @param array $parameters
     * @param \Symfony\Component\Console\Output\OutputInterface|null $outputBuffer
     * @param callable|null $onStart
     * @param bool $redirect_stdin_and_stdout
     * @param int $pipe_type
     * @param bool $enable_coroutine
     */
    public function __construct($command, $parameters, $outputBuffer, $onStart = null, $redirect_stdin_and_stdout = false, $pipe_type = SOCK_DGRAM, $enable_coroutine = false)
    {
        parent::__construct(
            $redirect_stdin_and_stdout,
            $pipe_type,
            $enable_coroutine
        );

        $this->command = $command;
        $this->parameters = $parameters;
        $this->outputBuffer = $outputBuffer;
        $this->onStart = $onStart;
    }

    /**
     *
     */
    protected function onStart()
    {
        if(is_callable($this->onStart)) {
            call_user_func_array($this->onStart, [$this]);
        }
    }

    /**
     * 执行队列消费
     *
     * @return void
     */
    public function handle()
    {
        \Illuminate\Support\Facades\Artisan::call($this->command, $this->parameters, $this->outputBuffer);
    }
}