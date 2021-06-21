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
    public function handle()
    {
        app('events')->listen(\Illuminate\Queue\Events\JobProcessing::class, [$this, 'onJobProcessing']);
        app('events')->listen(\Illuminate\Queue\Events\JobProcessed::class, [$this, 'onJobProcessed']);
        app('events')->listen(\Illuminate\Queue\Events\JobFailed::class, [$this, 'onJobFailed']);
        app('events')->listen(\Illuminate\Queue\Events\JobExceptionOccurred::class, [$this, 'onJobExceptionOccurred']);
        app('events')->listen(\Illuminate\Queue\Events\Looping::class, [$this, 'onLooping']);
        app('events')->listen(\Illuminate\Queue\Events\WorkerStopping::class, [$this, 'onWorkerStopping']);

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

    /**
     * @param \Illuminate\Queue\Events\JobProcessing $event
     */
    public function onJobProcessing(\Illuminate\Queue\Events\JobProcessing $event)
    {
        $this->trigger('onJobProcessing', $event);
    }

    /**
     * @param \Illuminate\Queue\Events\JobProcessed $event
     */
    public function onJobProcessed(\Illuminate\Queue\Events\JobProcessed $event)
    {
        $this->trigger('onJobProcessed', $event);
    }

    /**
     * @param \Illuminate\Queue\Events\JobFailed $event
     */
    public function onJobFailed(\Illuminate\Queue\Events\JobFailed $event)
    {
        $this->trigger('onJobFailed', $event);
    }

    /**
     * @param \Illuminate\Queue\Events\JobExceptionOccurred $event
     */
    public function onJobExceptionOccurred(\Illuminate\Queue\Events\JobExceptionOccurred $event)
    {
        $this->trigger('onJobExceptionOccurred', $event);
    }

    /**
     * @param \Illuminate\Queue\Events\Looping $event
     */
    public function onLooping(\Illuminate\Queue\Events\Looping $event)
    {
        $this->trigger('onLooping', $event);
    }

    /**
     * @param \Illuminate\Queue\Events\WorkerStopping $event
     */
    public function onWorkerStopping(\Illuminate\Queue\Events\WorkerStopping $event)
    {
        $this->trigger('onWorkerStopping', $event);
    }
}