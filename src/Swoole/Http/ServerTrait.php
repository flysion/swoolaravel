<?php namespace Lee2son\Swoolaravel\Swoole\Http;

use Swoole\Http\Request;
use Swoole\Http\Response;

trait ServerTrait
{
    use \Lee2son\Swoolaravel\Swoole\ServerTrait;

    protected function onRequest(Request $req, Response $resp)
    {
        $request = swoole_http_request_to_laravel_http_request($req);

        /**
         * @var \Lee2son\Swoolaravel\Http\Kernel $kernel
         */
        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);

        /**
         * @var \Illuminate\Http\Response $response
         */
        $response = $kernel->handle($request);

        $headers = $response->headers->allPreserveCaseWithoutCookies();
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $resp->header($name, $value);
            }
        }

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->isRaw()) {
                $resp->rawcookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiresTime(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly()
                );
            } else {
                $resp->cookie(
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

        $resp->status($response->status());
        $resp->end($response->getContent());

        $kernel->terminate($request, $response);

        return [$request, $response];
    }
}