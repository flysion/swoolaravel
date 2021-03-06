<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 接收到 UDP 数据包时回调此函数，发生在 worker 进程中。
 *
 * 注意：
 *  1.服务器同时监听 TCP/UDP 端口时，收到 TCP 协议的数据会回调 onReceive，收到 UDP 数据包回调 onPacket。
 *    服务器设置的 EOF 或 Length 等自动协议处理 (参考 TCP 粘包问题)，对 UDP 端口是无效的，因为 UDP 包本身存在消息边界，不需要额外的协议处理。
 *
 * @link https://wiki.swoole.com/#/server/events?id=onpacket onPacket
 * @link https://wiki.swoole.com/#/start/start_udp_server UDP 服务器
 */
class Packet
{
    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;
    /**
     * @var string 收到的数据内容，可能是文本或者二进制内容
     */
    public $data;

    /**
     * @var array 客户端信息包括 address/port/server_socket 等多项客户端信息数据，参考 UDP 服务器
     */
    public $clientInfo;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     * @param array $clientInfo 客户端信息包括 address/port/server_socket 等多项客户端信息数据，参考 UDP 服务器
     */
    public function __construct($server, $data, $clientInfo)
    {
        $this->server = $server;
        $this->data = $data;
        $this->clientInfo = $clientInfo;
    }
}