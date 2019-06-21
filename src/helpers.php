<?php
function swoole_request_to_laravel_request(\Swoole\Http\Request $req) : \Illuminate\Http\Request
{
    $_server = [];
    foreach($req->header as $k => $v)
    {
        $k = 'HTTP_' . str_replace('-', '_', strtoupper($k));
        $_server[$k] = $v;
    }

    foreach($req->server as $k => $v)
    {
        $k = strtoupper($k);
        $_server[$k] = $v;
    }

    return \Illuminate\Http\Request::create(
        $req->server['path_info'] . (@$req->server['query_string'] ? '?' . $req->server['query_string'] : ''),
        $req->server['request_method'],
        $req->post ?: [],
        $req->cookie ?: [],
        $req->files ?: [],
        $_server,
        $req->rawContent()
    );
}