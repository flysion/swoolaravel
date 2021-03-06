<?php

namespace Flysion\Swoolaravel\Swoole\Process;

/**
 * 创建一个 swoole 进程，用于消费 laravel 的队列
 */
class QueueWorker extends \Swoole\Process
{
    /**
     * @var string
     */
    protected $connection;

    /**
     * @var string
     */
    protected $queue;

    /**
     * @var \Illuminate\Queue\WorkerOptions
     */
    protected $workerOptions;

    /**
     * @var null|string
     */
    protected $processNamePrefix = 'swoolaravel-';

    /**
     * @param string $connection
     * @param string $queue
     * @param \Illuminate\Queue\WorkerOptions $workerOptions
     * @param string|null $processNamePrefix
     * @param null $redirect_stdin_and_stdout
     * @param null $pipe_type
     * @param null $enable_coroutine
     */
    public function __construct($connection, $queue, $workerOptions, $processNamePrefix = null, $redirect_stdin_and_stdout = null, $pipe_type = null, $enable_coroutine = null)
    {
        parent::__construct(
            [$this, 'handle'],
            $redirect_stdin_and_stdout,
            $pipe_type,
            $enable_coroutine
        );

        $this->connection = $connection;
        $this->queue = $queue;
        $this->workerOptions = $workerOptions;
        $this->processNamePrefix = $processNamePrefix ?? $this->processNamePrefix;
    }

    /**
     * 执行队列消费
     *
     * @return void
     */
    public function handle()
    {
        $quit = false;

        // 在 shutdown 关闭服务器时，会向用户进程发送 SIGTERM 信号，关闭用户进程
        pcntl_signal(SIGTERM/*15*/, function($signo) use(&$quit) {
            $quit = true;
        });

        // 设置进程名称

        swoole_set_process_name("{$this->processNamePrefix}queue");

        /**
         * @var \Illuminate\Queue\Worker $queue
         */
        $queue = app('queue.worker');

        while(!$quit) {
            $queue->runNextJob($this->connection, $this->queue, $this->workerOptions);
            pcntl_signal_dispatch();
        }
    }
}