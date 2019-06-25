<?php namespace Lee2son\Laravoole\Server;

use Illuminate\Support\Facades\Redis;
use Lee2son\Laravoole\Exceptions\InvalidEventException;
use Lee2son\Laravoole\Server;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;
use Swoole\Http\Serve as SwooleHttpServer;
use Swoole\Process as SwooleProcess;

class Http implements Server {

    const SWOOLE_SERVER = SwooleHttpServer::class;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $port;

    /**
     * @var array
     */
    public $settings;

    /**
     * @var int see https://wiki.swoole.com/wiki/page/353.html
     */
    public $process_mode;

    /**
     * @var int
     */
    public $sock_type;

    /**
     * @var SwooleHttpServer
     */
    protected $swoole_server = null;

    /**
     * Server constructor.
     */
    public function __construct()
    {
        $this->host = config('webserver.host');
        $this->port = config('webserver.port');
        $this->settings = config('webserver.settings');
        $this->process_mode = config('webserver.process_mode');
        $this->sock_type = config('webserver.sock_type');

        $server_name = static::SWOOLE_SERVER;

        $this->swoole_server = new $server_name($this->host, $this->port, $this->process_mode, $this->sock_type);
        $this->swoole_server->set($this->settings);

        if($this->process_mode !== SWOOLE_BASE) $this->on('Start');
        $this->on('ManagerStart');
        $this->on('WorkerStart');
        $this->on('Request');
    }

    /**
     * If the method does not exist, call the method of swoole_server
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->swoole_server, $name], $arguments);
    }

    /**
     * 注册回调 see https://wiki.swoole.com/wiki/page/330.html
     * @param string $event_name
     * @param callable|null $callback
     * @return void
     */
    public function on($event_name, callable $callback = null)
    {
        $method_name = 'on' . $event_name;
        if (method_exists($this, $method_name)) {
            $this->swoole_server->on($event_name, function () use($callback, $method_name) {
                $user_callback_args = $this->$method_name(...func_get_args());
                if(is_callable($callback) && is_array($user_callback_args)) $callback(...$user_callback_args);
            });
        } elseif(is_callable($callback)) {
            $this->swoole_server->on($event_name, $callback);
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
     * 可以通过$server->taskworker属性来判断当前是Worker进程还是Task进程
     * 设置了worker_num和task_worker_num超过1时，每个进程都会触发一次onWorkerStart事件，可通过判断$worker_id区分不同的工作进程（see https://wiki.swoole.com/wiki/page/235.html）
     * @param SwooleHttpServer $server
     * @param int $worker_id
     * @return [SwooleHttpServer $server, int $worker_id, int $task_id] $task_id=-1 则为 event worker 否则为 task worker
     */
    protected function onWorkerStart($server, $worker_id)
    {
        $task_id = max(-1, $worker_id - $this->settings['worker_num']);

        swoole_set_process_name($this->settings['process_name_prefix'] . ($task_id >= 0 ? "task-{$task_id}" : "event-{$worker_id}"));

        kernel_register();
        kernel_boostrap();

        app()->alias(Worker::class, 'laravoole.worker');
        app()->singleton(Worker::class);
        app('laravoole.worker')->worker_id = $worker_id;
        app('laravoole.worker')->task_id = $task_id;

        return [
            $server,
            $worker_id,
            $task_id
        ];
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