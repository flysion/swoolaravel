<?php

namespace Flysion\Swoolaravel\Listeners;

class RequestToLaravel
{
    /**
     * @param \Flysion\Swoolaravel\Swoole\Http\Server|\Flysion\Swoolaravel\Swoole\WebSocket\Server $server
     * @param \Flysion\Swoolaravel\Events\Request $event
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle($server, \Flysion\Swoolaravel\Events\Request $event)
    {
        /**
         * @var \Illuminate\Foundation\Http\Kernel $kernel
         */
        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);

        /**
         * @var \Illuminate\Http\Response $response
         */
        $response = $kernel->handle(
            $request = \Flysion\Swoolaravel\swoole_request_to_laravel_request($event->request)
        );

        $headers = $response->headers->allPreserveCaseWithoutCookies();
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $event->response->header($name, $value);
            }
        }

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->isRaw()) {
                $event->response->rawcookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiresTime(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly()
                );
            } else {
                $event->response->cookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiresTime(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly()
                );
            }
        }

        $event->response->status($response->status());
        $event->response->end($response->getContent());

        $kernel->terminate($request, $response);
    }
}