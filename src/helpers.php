<?php
/**
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
 * 注册内核实例，在子进程中调用，改变 app() 返回的类实例
 * @return void
 */
function kernel_register()
{
    app()->singleton(\Illuminate\Contracts\Http\Kernel::class, \Lee2son\Laravoole\Http\Kernel::class);
    app()->singleton(\Illuminate\Contracts\Console\Kernel::class, \Lee2son\Laravoole\Console\Kernel::class);
}

/**
 * 启动内核（加载整个框架）
 * @return void
 */
function kernel_boostrap()
{
    $consoleKernel = app()->make(\Illuminate\Contracts\Console\Kernel::class);
    $consoleKernel->bootstrap();
}