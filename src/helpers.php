<?php

namespace Flysion\Swoolaravel;

/**
 * \Swooler\Http\Request to \Illuminate\Http\Request
 *
 * @param \Swoole\Http\Request $request
 * @return \Illuminate\Http\Request
 */
function swoole_http_request_to_laravel_http_request(\Swoole\Http\Request $request) : \Illuminate\Http\Request
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
 * @param string
 * @param array $parameters
 * @param string $defaultMethod
 * @return mixed
 * @throws
 */
function call($callback, $parameters = [], $defaultMethod = 'handle')
{
    if(is_callable($callback)) {
        return call_user_func_array($callback, $parameters);
    }

    if(is_object($callback)) {
        return call_user_func_array([$callback, $defaultMethod], $parameters);
    }

    if(is_string($callback)) {
        if(strpos($callback, '@') === false) {
            $class = $callback;
            $method = $defaultMethod;
        } else {
            list($class, $method) = explode('@', $callback, 2);
        }

        return call([app()->make($class), $method], $parameters);
    }

    return call_user_func_array($callback, $parameters);
}