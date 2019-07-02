<?php namespace Lee2son\Laravoole\Server;

use Lee2son\Laravoole\Server;
use Swoole\Http\Request as SwooleHttpRequest; // see https://wiki.swoole.com/wiki/page/328.html
use Swoole\Http\Response as SwooleHttpResponse; // see https://wiki.swoole.com/wiki/page/329.html
use Swoole\Http\Serve as SwooleHttpServer; // see https://wiki.swoole.com/wiki/page/326.html

class Http implements Server {

    const SWOOLE_SERVER = SwooleHttpServer::class;

    /**
     * @var string $host see https://wiki.swoole.com/wiki/page/326.html
     */
    public $host;

    /**
     * @var string $port see https://wiki.swoole.com/wiki/page/326.html
     */
    public $port;

    /**
     * @var array $settings see https://wiki.swoole.com/wiki/page/274.html
     */
    public $settings;

    /**
     * @var int see https://wiki.swoole.com/wiki/page/353.html
     */
    public $processMode;

    /**
     * @var int $sockType see https://wiki.swoole.com/wiki/page/14.html
     */
    public $sockType;

    /**
     * @var SwooleHttpServer $swooleServer see https://wiki.swoole.com/wiki/page/326.html
     */
    protected $swooleServer = null;

    /**
     * Http constructor.
     * @param string $host see https://wiki.swoole.com/wiki/page/326.html
     * @param string $port see https://wiki.swoole.com/wiki/page/326.html
     * @param array $settings see https://wiki.swoole.com/wiki/page/274.html
     * @param int $processMode see https://wiki.swoole.com/wiki/page/353.html
     * @param int $sockType see https://wiki.swoole.com/wiki/page/14.html
     */
    public function __construct($host, $port, $settings, $processMode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP)
    {
        $this->host = $host;
        $this->port = $port;
        $this->settings = $settings;
        $this->processMode = $processMode;
        $this->sockType = $sockType;

        $swooleServerName = static::SWOOLE_SERVER;
        $this->swooleServer = new $swooleServerName($this->host, $this->port, $this->processMode, $this->sockType);
        $this->swooleServer->set($this->settings);

        if($this->processMode !== SWOOLE_BASE) {
            $this->on('Start');
        }
        $this->on('ManagerStart');
        $this->on('WorkerStart');
        $this->on('Request');
    }

    /**
     * 如果调用的方法不存在，则调用 SwooleHttpServer 的方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->swooleServer, $name], $arguments);
    }

    /**
     * 注册 SwooleHttpServer 事件。 see https://wiki.swoole.com/wiki/page/330.html
     * 如果类已经实现了 on{$eventName} 方法，则先调用该方法，该方法返回值作为 $callback 的参数，这等于是重写 SwooleHttpServer 事件
     * @param string $eventName
     * @param callable|null $callback
     * @return void
     */
    public function on($eventName, callable $callback = null)
    {
        $methodName = 'on' . $eventName;

        if (method_exists($this, $methodName)) {
            $this->swooleServer->on($eventName, function () use($callback, $methodName) {
                $args = $this->$methodName(...func_get_args());

                if(is_callable($callback) && is_array($args)) {
                    call_user_func_array($callback, $args);
                }
            });
        } elseif(is_callable($callback)) {
            $this->swooleServer->on($eventName, $callback);
        }
    }

    /**
     * 启动后在主进程（master）的主线程回调此函数 see https://wiki.swoole.com/wiki/page/p-event/onStart.html
     * onWorkerStart和onStart回调是在不同进程中并行执行的，不存在先后顺序
     * 在onStart中创建的全局资源对象不能在Worker进程中被使用，因为发生onStart调用时，worker进程已经创建好了，新创建的对象在主进程内，Worker进程无法访问到此内存区域，因此全局对象创建的代码需要放置在Server::start之前
     * @param SwooleHttpServer $server
     * @return [SwooleHttpServer $server]
     */
    protected function onStart($server)
    {
        swoole_set_process_name($this->settings['process_name_prefix'] . 'master');

        return [$server];
    }

    /**
     * 当管理进程启动时调用它 see https://wiki.swoole.com/wiki/page/190.html
     * @param SwooleHttpServer $server
     * @return [SwooleHttpServer $server]
     */
    protected function onManagerStart($server)
    {
        swoole_set_process_name($this->settings['process_name_prefix'] . 'manager');

        return [$server];
    }

    /**
     * 此事件在Worker进程/Task进程启动时发生。这里创建的对象可以在进程生命周期内使用 see https://wiki.swoole.com/wiki/page/p-event/onWorkerStart.html
     * 发生致命错误或者代码中主动调用exit时，Worker/Task进程会退出，管理进程会重新创建新的进程。这可能导致死循环，不停地创建销毁进程
     * onWorkerStart/onStart是并发执行的，没有先后顺序
     * 可以通过 $server->taskworker 属性来判断当前是worker进程还是task进程
     * 每个worker进程都会触发一次 onWorkerStart 事件，可通过判断 $workerId 区分不同的工作进程（see https://wiki.swoole.com/wiki/page/235.html）
     * @param SwooleHttpServer $server
     * @param int $worker_id
     * @return [SwooleHttpServer $server, int $workerId, int $taskId] $taskId=-1 则为 event worker 否则为 task worker
     */
    protected function onWorkerStart($server, $workerId)
    {
        $taskId = max(-1, $workerId - $this->settings['worker_num']);

        swoole_set_process_name($this->settings['process_name_prefix'] . "worker-{$workerId}-" .  ($taskId >= 0 ? "task-{$taskId}" : "event-{$workerId}"));

        kernel_register();
        kernel_boostrap();

        // 为每个进程注入一个 Worker 单例
        app()->alias(Worker::class, 'laravoole.worker');
        app()->singleton(Worker::class, function () use($workerId, $taskId) {
            return new Worker($workerId, $taskId);
        });

        return [$server, $workerId, $taskId];
    }

    /**
     * 在收到一个完整的Http请求后，会回调此函数 see https://wiki.swoole.com/wiki/page/330.html
     * 在onRequest回调函数返回时底层会销毁$request和$response对象，如果未执行$response->end()操作，底层会自动执行一次$response->end("")
     * @param SwooleHttpRequest $request
     * @param SwooleHttpResponse $response
     * @return [Request $request, Response $response]
     */
    protected function onRequest(SwooleHttpRequest $req, SwooleHttpResponse $resp)
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

        return [$request, $response];
    }
}