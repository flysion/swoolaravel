<?php namespace Lee2son\Swoolaravel\Swoole;

class Server extends \Swoole\Server
{
    use ServerTrait;

    public function __construct($host, $port = null, $mode = null, $sock_type = null)
    {
        parent::__construct($host, $port, $mode, $sock_type);

        $this->on('WorkerStart', null);
    }
}