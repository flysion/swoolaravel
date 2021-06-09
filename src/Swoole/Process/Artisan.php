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
     * @param string $command
     * @param array $parameters
     * @param \Symfony\Component\Console\Output\OutputInterface|null $outputBuffer
     * @param callable|null $onStart
     * @param bool $redirect_stdin_and_stdout
     * @param int $pipe_type
     * @param bool $enable_coroutine
     */
    public function __construct($command, $parameters, $outputBuffer, $redirect_stdin_and_stdout = false, $pipe_type = SOCK_DGRAM, $enable_coroutine = false)
    {
        parent::__construct(
            $redirect_stdin_and_stdout,
            $pipe_type,
            $enable_coroutine
        );

        $this->command = $command;
        $this->parameters = $parameters;
        $this->outputBuffer = $outputBuffer;
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