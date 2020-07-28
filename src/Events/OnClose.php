<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onClose()
 */
class OnClose
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
     * @var int 来自那个 reactor 线程，主动 close 关闭时为负数
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