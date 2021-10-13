<?php

namespace Flysion\Swoolaravel\Swoole\Process;

/**
 * 创建一个 swoole 进程，用于消费 laravel 的队列
 */
class QueueWorker extends Process
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
     * @param string $connection
     * @param string $queue
     * @param \Illuminate\Queue\WorkerOptions $workerOptions
     * @param bool $redirect_stdin_and_stdout
     * @param int $pipe_type
     * @param bool $enable_coroutine
     */
    public function __construct($connection, $queue, $workerOptions, $redirect_stdin_and_stdout = false, $pipe_type = SOCK_DGRAM, $enable_coroutine = false)
    {
        parent::__construct(
            $redirect_stdin_and_stdout,
            $pipe_type,
            $enable_coroutine
        );

        $this->connection = $connection;
        $this->queue = $queue;
        $this->workerOptions = $workerOptions;
    }

    /**
     * 执行队列消费
     *
     * @return void
     */
    protected function handle()
    {
        /**
         * @var \Illuminate\Queue\Worker $queue
         */
        $queue = app('queue.worker');

        try {
            $queue->daemon($this->connection, $this->queue, $this->workerOptions);
        } catch (\Swoole\ExitException $e) {
            return ;
        }
    }
}