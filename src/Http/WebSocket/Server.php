<?php
namespace Lee2son\Laravoole\Http\WebSocket;

use Swoole\WebSocket\Server as SwooleWebSocketServer;

class Server extends \Lee2son\Laravoole\Http\Server {

    const SWOOLE_SERVER = SwooleWebSocketServer::class;

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
        parent::__construct($host, $port, $config, $process_mode, $sock_type);
    }
}