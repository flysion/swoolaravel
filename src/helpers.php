<?php

namespace Flysion\Swoolaravel;

const events = [
    'start' => Events\Start::class,
    'shutdown' => Events\Shutdown::class,
    'managerstart' => Events\ManagerStart::class,
    'managerstop' => Events\ManagerStop::class,
    'workerstart' => Events\WorkerStart::class,
    'workerstop' => Events\WorkerStop::class,
    'workerexit' => Events\WorkerExit::class,
    'workererror' => Events\WorkerError::class,
    'task' => Events\Task::class,
    'finish' => Events\Finish::class,
    'connect' => Events\Connect::class,
    'open' => Events\Open::class,
    'close' => Events\Close::class,
    'packet' => Events\Packet::class,
    'request' => Events\Request::class,
    'message' => Events\Message::class,
    'receive' => Events\Receive::class,
    'pipemessage' => Events\PipeMessage::class,
    'handshake' => Events\HandShake::class,
    'beforereload' => Events\BeforeReload::class,
    'afterreload' => Events\AfterReload::class,
];

/**
 * \Swooler\Http\Request to \Illuminate\Http\Request
 *
 * @param \Swoole\Http\Request $request
 * @return \Illuminate\Http\Request
 */
function swoole_request_to_laravel_request(\Swoole\Http\Request $request) : \Illuminate\Http\Request
{
    $server = $_SERVER;
    foreach($request->header as $k => $v)
    {
        $k = 'HTTP_' . str_replace('-', '_', strtoupper($k));
        $server[$k] = $v;
    }

    foreach($request->server as $k => $v)
    {
        $k = strtoupper($k);
        $server[$k] = $v;
    }

    return \Illuminate\Http\Request::create(
        $request->server['path_info'] . (@$request->server['query_string'] ? '?' . $request->server['query_string'] : ''),
        $request->server['request_method'],
        $request->post ?: [],
        $request->cookie ?: [],
        $request->files ?: [],
        $server,
        $request->rawContent()
    );
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