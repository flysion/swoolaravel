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
        // 加载一个新的app替换老的app
        // 这里主要作用是重置框架里的一些东西（清除容器）

        $app = require base_path('/bootstrap/app.php');
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $app->instance('server', $this);

        // 重新注册 app 实例，通过 app() 方法可获取该实例

        \Illuminate\Foundation\Application::setInstance($app);

        //

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