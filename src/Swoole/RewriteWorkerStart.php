<?php namespace Lee2son\Laravoole\Swoole;

trait RewriteWorkerStart
{
    protected function onWorkerStart($server, $workerId)
    {
        $taskId = max(-1, $workerId - $this->setting['worker_num']);

        kernel_boostrap();

        return [$server, $workerId, $taskId];
    }
}