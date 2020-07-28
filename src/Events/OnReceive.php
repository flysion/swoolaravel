<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onReceive()
 */
class OnReceive
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
     * @var string 收到的数据内容，可能是文本或者二进制内容
     */
    protected $data;

    /**
     * @param \Swoole\Server
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function __construct($server, $fd, $reactorId, $data)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
        $this->data = $data;
    }
}