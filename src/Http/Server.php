<?php
namespace Lee2son\Laravoole\Http;

use Swoole\Http\Server as SwooleHttpServer;
use Illuminate\Contracts\Http\Kernel;
use Lee2son\Laravoole\HttpKernel;
use Lee2son\Laravoole\ConsoleKernel;

class Server implements \Lee2son\Laravoole\Server {

    /**
     * @var callback
     */
    protected $onStart = null;

    /**
     * @var callback
     */
    protected $onShutdown = null;

    /**
     * @var callback
     */
    protected $onManagerStart = null;

    /**
     * @var callback
     */
    protected $onManagerStop = null;

    /**
     * @var callback
     */
    protected $onWorkerStart = null;

    /**
     * @var callback
     */
    protected $onWorkerStop = null;

    /**
     * @var callback
     */
    protected $onWorkerExit = null;

    /**
     * @var callback
     */
    protected $onWorkerError = null;

    /**
     * @var callback
     */
    protected $onClose = null;

    /**
     * @var callback
     */
    protected $onTask = null;

    /**
     * @var callback
     */
    protected $onTaskCoroutine = null;

    /**
     * @var callback
     */
    protected $onFinish = null;

    /**
     * @var callback
     */
    protected $onPipeMessage = null;

    /**
     * @var callback
     */
    protected $onRequest = null;

    protected $config;

    private $process_mode;

    private $sock_type;

    /**
     * @var null|SwooleHttpServer
     */
    private $swoole_server = null;

    /**
     * Server constructor.
     * @param $host
     * @param $port
     * @param $config
     * @param null $process_mode
     * @param null $sock_type
     */
    public function __construct($host, $port, $config, $process_mode = null, $sock_type = null)
    {
        $this->config = $config;
        $this->process_mode = $process_mode;
        $this->sock_type = $sock_type;

        $this->swoole_server = new \Swoole\Http\Server($host, $port, $process_mode, $sock_type);
        $this->swoole_server->set($config);

//        if($process_mode !== SWOOLE_BASE) parent::on('Start', function($server) { $this->onStart($server); });
//        parent::on('ManagerStart', function($server) { $this->onManagerStart($server); });
//        parent::on('WorkerStart', function($server, $worker_id) { $this->onWorkerStart($server, $worker_id); });
    }

//    public function on($event_name, callable $callback)
//    {
//        $event_name = 'on' . $event_name;
//        $this->$event_name = $callback;
//    }
//
//    public function __call($name, $arguments)
//    {
//        if(substr($name, 0, 2) === 'on' and property_exists($this, $name))
//        {
//            $this->$name = $arguments[0];
//        }
//    }

    protected function onStart($server)
    {
        if(@$this->config['master_name']) {
            swoole_set_process_name($this->config['master_name']);
        }
    }

    protected function onManagerStart($server)
    {
        if(@$this->config['manager_name']) {
            swoole_set_process_name($this->config['manager_name']);
        }

        global $app;

        $consoleKernel = $app->make(Kernel::class);
        $consoleKernel->bootstrap();
    }

    protected function onWorkerStart($server, $worker_id)
    {
        if(@$this->config['task_name_prefix'] && $worker_id >= $this->config['worker_num']) {
            swoole_set_process_name($this->config['task_name_prefix'] . $worker_id);
        } elseif(@$this->config['event_name_prefix']) {
            swoole_set_process_name($this->config['event_name_prefix'] . $worker_id);
        }
    }

    public function loadKernel()
    {
        global $app;

        $app->singleton(Kernel::class, HttpKernel::class);
        $app->singleton(Kernel::class, ConsoleKernel::class);
    }
}