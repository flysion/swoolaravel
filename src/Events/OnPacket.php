<?php

namespace Lee2son\Swoolaravel\Events;

/**
 * @see \Lee2son\Swoolaravel\Swoole\Server::onPacket()
 */
class OnPacket
{
    /**
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * @var string 收到的数据内容，可能是文本或者二进制内容
     */
    protected $data;

    /**
     * @var array 客户端信息包括 address/port/server_socket 等多项客户端信息数据，参考 UDP 服务器
     * @link https://wiki.swoole.com/#/start/start_udp_server UDP 服务器
     */
    protected $clientInfo;

    /**
     * @param \Swoole\Server
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function __construct($server, $data, $clientInfo)
    {
        $this->server = $server;
        $this->data = $data;
        $this->clientInfo = $clientInfo;
    }
}