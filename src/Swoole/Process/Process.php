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
        $callback = function() {
            $server = app('server');

            $app = require base_path('/bootstrap/app.php');
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            $app->instance('server', $server);
            $app->instance('process', $this);

            $this->handle();
        };

        parent::__construct(
            $callback,
            $redirect_stdin_and_stdout,
            $pipe_type,
            $enable_coroutine
        );
    }

    /**
     * @return void
     */
    abstract protected function handle();
}