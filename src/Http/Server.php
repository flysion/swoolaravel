<?php
namespace Lee2son\Laravoole\Http;

use Lee2son\Laravoole\Exceptions\InvalidEventException;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;
use Lee2son\Laravoole\HttpKernel;
use Lee2son\Laravoole\ConsoleKernel;

class Server implements \Lee2son\Laravoole\Server {

    const SWOOLE_SERVER = SwooleHttpServer::class;

    /**
     * @var callback
     */
    protected $onWorkerStart = null;

    /**
     * @var array $settings by swoole_server::set
     */
    protected $settings;

    /**
     * @var int see https://wiki.swoole.com/wiki/page/353.html
     */
    protected $process_mode;

    /**
     * @var int
     */
    protected $sock_type;

    /**
     * @var SwooleHttpServer
     */
    protected $swoole_server = null;

    /**
     * Server constructor.
     * @param $host
     * @param $port
     * @param $config
     * @param null $process_mode
     * @param null $sock_type
     */
    public function __construct($host, $port, $settings, $process_mode = null, $sock_type = null)
    {
        $this->settings = $settings;
        $this->process_mode = $process_mode;
        $this->sock_type = $sock_type;

        $server_name = static::SWOOLE_SERVER;

        $this->swoole_server = new $server_name($host, $port, $process_mode, $sock_type);
        $this->swoole_server->set($settings);

        $this->swoole_server->on('WorkerStart', function($server, $worker_id) { $this->onWorkerStart($server, $worker_id); });
        $this->swoole_server->on('Request', function(SwooleHttpRequest $req, SwooleHttpResponse $resp) { $this->onRequest($req, $resp); });
    }

    /**
     * register kernel
     * @return void
     */
    public function registerKernel()
    {
        app()->singleton(\Illuminate\Contracts\Http\Kernel::class, HttpKernel::class);
        app()->singleton(\Illuminate\Contracts\Console\Kernel::class, ConsoleKernel::class);
    }

    /**
     * kernel boostrap
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @return void
     */
    public function kernelBoostrap()
    {
        $consoleKernel = app()->make(\Illuminate\Contracts\Console\Kernel::class);
        $consoleKernel->bootstrap();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->swoole_server, $name], $arguments);
    }

    public function on($event_name, callable $callback)
    {
        if($event_name === 'Request') {
            throw new InvalidEventException("\"{$event_name}\" is not allow");
        }

        $prototype = 'on' . $event_name;
        if (property_exists($this, $prototype)) {
            $this->$prototype = $callback;
        } else {
            $this->swoole_server->on($event_name, $callback);
        }
    }

    protected function onWorkerStart($server, $worker_id)
    {
        $isTaskWorker = $worker_id >= $this->settings['worker_num'];

        $this->registerKernel();
        $this->kernelBoostrap();

        if (is_callable($this->onWorkerStart)) {
            call_user_func($this->onWorkerStart, $server, $worker_id, $isTaskWorker);
        }
    }

    protected function onRequest(SwooleHttpRequest $req, SwooleHttpResponse $resp)
    {
        $request = swoole_request_to_laravel_request($req);

        /**
         * @var HttpKernel
         */
        $httpKernel = app()->make(Illuminate\Contracts\Http\Kernel::class);

        /**
         * @var $response \Illuminate\Http\Response
         */
        $response = $httpKernel->handle($request);

        // http headers
        $headers = $response->headers->allPreserveCaseWithoutCookies();
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $resp->header($name, $value);
            }
        }

        // http cookies
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

        // http status
        $resp->status($response->status());

        // http body
        $resp->end($response->getContent());

        // laravel terminate
        $httpKernel->terminate($request, $response);
    }
}