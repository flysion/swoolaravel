<?php namespace Lee2son\Swoolaravel\Swoole\WebSocket;

class Server extends \Swoole\WebSocket\Server
{
    use ServerTrait;

    public function __construct($host, $port = null, $mode = null, $sock_type = null)
    {
        parent::__construct($host, $port, $mode, $sock_type);

        $this->on('WorkerStart', null);
        //$this->on('Request', null);
        $this->on('Open', null);
        $this->on('Message', null);
    }
}