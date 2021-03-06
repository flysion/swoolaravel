<?php

namespace Flysion\Swoolaravel\Swoole\Process;
/**
 * 创建一个 swoole 进程，用于调用 laravel 命令
 */
class Artisan extends \Swoole\Process
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
     * @var null|string
     */
    protected $processNamePrefix = 'swoolaravel-';

    /**
     * @param string $command
     * @param array $parameters
     * @param \Symfony\Component\Console\Output\OutputInterface|null $outputBuffer
     * @param string|null $processNamePrefix
     * @param null $redirect_stdin_and_stdout
     * @param null $pipe_type
     * @param null $enable_coroutine
     */
    public function __construct($command, $parameters, $outputBuffer, $processNamePrefix = null, $redirect_stdin_and_stdout = null, $pipe_type = null, $enable_coroutine = null)
    {
        parent::__construct(
            [$this, 'handle'],
            $redirect_stdin_and_stdout,
            $pipe_type,
            $enable_coroutine
        );

        $this->command = $command;
        $this->parameters = $parameters;
        $this->outputBuffer = $outputBuffer;
        $this->processNamePrefix = $processNamePrefix ?? $this->processNamePrefix;
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