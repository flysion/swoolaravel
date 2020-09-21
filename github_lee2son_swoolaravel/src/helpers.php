<?php
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