<?php namespace Lee2son\Swoolaravel\Swoole;

use Lee2son\Swoolaravel\Server\Worker;

trait _Server
{
    public function on($event, callable $callback)
    {
        $method = 'on' . ucfirst($event);
        if (method_exists($this, $method)) {
            $callback = function () use($callback, $method) {
                $args = func_get_args();
                $return = call_user_func_array([$this, $method], $args);
                if($return !== false && is_callable($callback)) {
                    call_user_func_array($callback, is_array($return) ? $return : $args);
                }
            };
        }

        parent::on($event, $callback);
    }

    protected function onWorkerStart($server, $workerId)
    {
        register_kernel();

        $taskId = max(-1, $workerId - $this->setting['worker_num']);
        if($taskId >= 0) {
            bootstrap_kernel(\Illuminate\Contracts\Console\Kernel::class);
        } else {
            bootstrap_kernel(\Illuminate\Contracts\Http\Kernel::class);
        }

        app()->alias(Worker::class, 'swoolaravel.worker');
        app()->singleton(Worker::class, function() use($workerId, $taskId) {
            return new Worker($workerId, $taskId);
        });

        return [$server, $workerId, $taskId];
    }
}