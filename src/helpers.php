<?php
/**
 * \Swooler\Http\Request to \Illuminate\Http\Request
 * @param \Swoole\Http\Request $req
 * @return \Illuminate\Http\Request
 */
function swoole_http_request_to_laravel_http_request(\Swoole\Http\Request $req) : \Illuminate\Http\Request
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
 * 注册 laravel 内核，在工作进程启动时调用
 */
function register_kernel()
{
    app()->singleton(\Illuminate\Contracts\Http\Kernel::class, \Lee2son\Swoolaravel\Http\Kernel::class);
    app()->singleton(\Illuminate\Contracts\Console\Kernel::class, \Lee2son\Swoolaravel\Console\Kernel::class);
}

/**
 * 内核引导，在工作进程启动时调用
 * @param string $kernel
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 */
function bootstrap_kernel($kernel = \Illuminate\Contracts\Console\Kernel::class)
{
    $kernel = app()->make($kernel);
    $kernel->bootstrap();
}