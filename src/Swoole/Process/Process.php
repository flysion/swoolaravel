<?php

namespace Flysion\Swoolaravel\Swoole\Process;

/**
 * @link https://wiki.swoole.com/#/process/process?id=process
 */
abstract class Process extends \Swoole\Process
{
    /**
     * @param bool $redirect_stdin_and_stdout
     * @param int $pipe_type
     * @param bool $enable_coroutine
     */
    public function __construct($redirect_stdin_and_stdout = false, $pipe_type = SOCK_DGRAM, $enable_coroutine = false)
    {
        parent::__construct(
            function() { $this->_handle(); },
            $redirect_stdin_and_stdout,
            $pipe_type,
            $enable_coroutine
        );
    }

    /**
     *
     */
    protected function _handle()
    {
        $this->onStart();
        $this->handle();
    }

    /**
     * @return mixed
     */
    abstract public function handle();

    /**
     *
     */
    protected function onStart()
    {

    }
}