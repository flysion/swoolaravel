<?php namespace Lee2son\Swoolaravel\Swoole\Http;

/**
 * @link https://wiki.swoole.com/#/http_server
 */
trait Server
{
    use \Lee2son\Swoolaravel\Swoole\Server;

    /**
     * @link https://wiki.swoole.com/#/http_server?id=on onRequest
     * @link https://wiki.swoole.com/#/http_server?id=httprequest \Swoole\Http\Request
     * @link https://wiki.swoole.com/#/http_server?id=httpresponse \Swoole\Http\Response
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onRequest($request, $response)
    {
        /**
         * @var \Illuminate\Foundation\Http\Kernel $kernel
         */
        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);

        /**
         * @var \Illuminate\Http\Response $laravelResponse
         */
        $laravelResponse = $kernel->handle(
            $laravelRequest = swoole_http_request_to_laravel_http_request($request)
        );

        $headers = $laravelResponse->headers->allPreserveCaseWithoutCookies();
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }

        foreach ($laravelResponse->headers->getCookies() as $cookie) {
            if ($cookie->isRaw()) {
                $response->rawcookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiresTime(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly()
                );
            } else {
                $response->cookie(
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

        $response->status($laravelResponse->status());
        $response->end($laravelResponse->getContent());

        $kernel->terminate($laravelRequest, $laravelResponse);
    }
}