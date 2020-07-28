<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\WebSocket\Server::onHandShake()
 */
class OnHandShake
{
    /**
     * @var \Swoole\Http\Request
     */
    protected $request;

    /**
     * @var \Swoole\Http\Response
     */
    protected $response;

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}