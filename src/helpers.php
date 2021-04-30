<?php

namespace Flysion\Swoolaravel;

use Symfony\Component\HttpFoundation\HeaderBag;

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
    foreach($request->server as $k => $v)
    {
        $k = strtoupper($k);
        $server[$k] = $v;
    }

    $req = \Illuminate\Http\Request::create(
        $request->server['path_info'] . (@$request->server['query_string'] ? '?' . $request->server['query_string'] : ''),
        $request->server['request_method'],
        $request->post ?: [],
        $request->cookie ?: [],
        $request->files ?: [],
        $server,
        $request->rawContent()
    );

    $req->headers = new HeaderBag($request->header);

    return $req;
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