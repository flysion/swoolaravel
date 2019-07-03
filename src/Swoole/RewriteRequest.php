<?php namespace Lee2son\Laravoole\Swoole;

trait RewriteRequest
{
    protected function onRequest(Request $req, Response $resp)
    {
        $request = swoole_request_to_laravel_request($req);

        /**
         * @var \Lee2son\Laravoole\Http\Kernel $kernel
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
    }
}