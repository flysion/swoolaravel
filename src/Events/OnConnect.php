<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onConnect()
 */
class OnConnect
{
    /**
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * @var int 连接的文件描述符
     */
    protected $fd;

    /**
     * @var int 连接所在的 Reactor 线程 ID
     */
    protected $reactorId;

    /**
     * @param \Swoole\Server
     * @param int $fd
     * @param int $reactorId
     */
    public function __construct($server, $fd, $reactorId)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }
}