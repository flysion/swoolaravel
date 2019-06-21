<?php namespace Lee2son\Laravoole\Http\WebSocket;

use Swoole\WebSocket\Server as SwooleWebSocketServer;

class Server extends \Lee2son\Laravoole\Http\Server {

    const SWOOLE_SERVER = SwooleWebSocketServer::class;

    /**
     * Server constructor.
     * @param string $host
     * @param string $port
     * @param array $settings
     * @param int $process_mode see https://wiki.swoole.com/wiki/page/353.html
     * @param int $sock_type
     */
    public function __construct($host, $port, $settings, $process_mode = null, $sock_type = null)
    {
        parent::__construct($host, $port, $settings, $process_mode, $sock_type);
    }
}