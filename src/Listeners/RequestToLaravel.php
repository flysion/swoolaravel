<?php

namespace Lee2son\Swoolaravel\Listeners;

class RequestToLaravel
{
    /**
     * @param \Lee2son\Swoolaravel\Swoole\Http\Server|\Lee2son\Swoolaravel\Swoole\WebSocket\Server $server
     * @param \Lee2son\Swoolaravel\Events\Request $event
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle($server, \Lee2son\Swoolaravel\Events\Request $event)
    {
        /**
         * @var \Illuminate\Foundation\Http\Kernel $kernel
         */
        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);

        /**
         * @var \Illuminate\Http\Response $response
         */
        $response = $kernel->handle(
            $request = \Lee2son\Swoolaravel\swoole_http_request_to_laravel_http_request($event->request)
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