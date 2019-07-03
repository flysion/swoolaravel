<?php
/**
 * \Swooler\Http\Request to \Illuminate\Http\Request
 * @param \Swoole\Http\Request $req
 * @return \Illuminate\Http\Request
 */
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

/**
 * 在工作进程启动时调用，可以用来启动 laravel 内核（方能调用 laravel 方法）
 */
function kernel_boostrap()
{
    app()->singleton(\Illuminate\Contracts\Http\Kernel::class, \Lee2son\Laravoole\Http\Kernel::class);
    app()->singleton(\Illuminate\Contracts\Console\Kernel::class, \Lee2son\Laravoole\Console\Kernel::class);

    $consoleKernel = app()->make(\Illuminate\Contracts\Console\Kernel::class);
    $consoleKernel->bootstrap();
}