<?php

namespace Flysion\Swoolaravel\Swoole;

/**
 * @mixin \Flysion\Swoolaravel\Swoole\Server
 * @mixin \Flysion\Swoolaravel\Swoole\Http\Server
 * @mixin \Flysion\Swoolaravel\Swoole\WebSocket\Server
 */
trait EnableHttp
{
    /**
     * @param array $setting
     * @return array $setting
     */
    protected function bootEnableHttpStrap($setting)
    {
        $setting['open_http_protocol'] = true;

        $this->on('request', function($server, \Flysion\Swoolaravel\Events\Request $event) {
            $this->requestToLaravel($event);
        });

        return $setting;
    }

    /**
     * @param \Flysion\Swoolaravel\Events\Request $event
     * @throws
     */
    protected function requestToLaravel(\Flysion\Swoolaravel\Events\Request $event)
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