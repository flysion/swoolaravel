<?php
namespace Lee2son\Laravoole\Http\WebSocket;

class Server extends \Lee2son\Laravoole\Http\Server {

    /**
     * @var callback
     */
    protected $onOpen = null;

    /**
     * @var callback
     */
    protected $onMessage = null;

    /**
     * @var callback
     */
    protected $onHandShake = null;

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