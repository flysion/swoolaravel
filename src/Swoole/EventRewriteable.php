<?php namespace Lee2son\Laravoole\Swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;

trait EventRewriteable {
    public function on($event, callable $callback)
    {
        $method = 'on' . $event;
        if (method_exists($this, $method)) {
            $callback = function () use($callback, $method) {
                $arguments = call_user_func_array([$this, $method], func_get_args());
                if(is_array($arguments)) {
                    call_user_func_array($callback, $arguments);
                }
            };
        }

        parent::on($event, $callback);
    }
}