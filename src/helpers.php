<?php

namespace Flysion\Swoolaravel;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ServerBag;

const events = [
    'start' => Events\Start::class,
    'shutdown' => Events\Shutdown::class,
    'managerStart' => Events\ManagerStart::class,
    'managerStop' => Events\ManagerStop::class,
    'workerStart' => Events\WorkerStart::class,
    'workerStop' => Events\WorkerStop::class,
    'workerExit' => Events\WorkerExit::class,
    'workerError' => Events\WorkerError::class,
    'task' => Events\Task::class,
    'finish' => Events\Finish::class,
    'connect' => Events\Connect::class,
    'open' => Events\Open::class,
    'close' => Events\Close::class,
    'packet' => Events\Packet::class,
    'request' => Events\Request::class,
    'message' => Events\Message::class,
    'receive' => Events\Receive::class,
    'pipeMessage' => Events\PipeMessage::class,
    'handShake' => Events\HandShake::class,
    'beforeReload' => Events\BeforeReload::class,
    'afterReload' => Events\AfterReload::class,
];

/**
 * \Swooler\Http\Request to \Illuminate\Http\Request
 *
 * @param \Swoole\Http\Request $request
 * @return \Illuminate\Http\Request
 */
function swoole_request_to_laravel_request(\Swoole\Http\Request $request) : \Illuminate\Http\Request
{
    $server = [];

    foreach($request->server as $k => $v)
    {
        $k = strtoupper($k);
        $server[$k] = $v;
    }

    foreach($request->header as $k => $v) {
        $k = 'HTTP_' . strtoupper(str_replace('-', '_', $k));
        $server[$k] = $v;
    }

    if(isset($server['HTTP_CONTENT_TYPE'])) {
        $server['CONTENT_TYPE'] = $server['HTTP_CONTENT_TYPE'];
    }

    $uri = $request->server['path_info'];
    if($request->get) {
        $uri .= '?' . http_build_query($request->get);
    }

    $swooleRequest = \Illuminate\Http\Request::createFromBase(
        \Illuminate\Http\Request::create(
            $uri,
            $request->server['request_method'],
            $request->post ?: [],
            $request->cookie ?: [],
            $request->files ?: [],
            $server,
            $request->rawContent()
        )
    );

    return $swooleRequest;
}

/**
 * 应用程序是否运行在 swoole 里
 *
 * @return bool
 */
function running_in_swoole()
{
    return env('APP_RUNNING_IN_SWOOLE');
}