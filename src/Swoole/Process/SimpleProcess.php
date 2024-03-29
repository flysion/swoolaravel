<?php

namespace Flysion\Swoolaravel\Swoole\Process;

/**
 * @link https://wiki.swoole.com/#/process/process?id=process
 */
class SimpleProcess extends Process
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @param callable $callback
     * @param bool $redirect_stdin_and_stdout
     * @param int $pipe_type
     * @param bool $enable_coroutine
     */
    public function __construct($callback, $redirect_stdin_and_stdout = false, $pipe_type = SOCK_DGRAM, $enable_coroutine = false)
    {
        parent::__construct(
            $redirect_stdin_and_stdout,
            $pipe_type,
            $enable_coroutine
        );

        $this->callback = $callback;
    }

    /**
     * @return void
     */
    protected function handle()
    {
        call_user_func($this->callback, $this);
    }
}