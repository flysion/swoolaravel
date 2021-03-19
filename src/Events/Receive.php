<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 接收到数据时回调此函数，发生在 worker 进程中。
 * 默认情况下，同一个 fd 会被分配到同一个 Worker 中，所以数据可以拼接起来。
 * 使用 dispatch_mode = 3 时。 请求数据是抢占式的，同一个 fd 发来的数据可能会被分到不同的进程。所以无法使用上述的数据包拼接方法
 *
 * 注意：
 *  1.未开启自动协议选项，onReceive 单次收到的数据最大为 64K
 *  2.开启了自动协议处理选项，onReceive 将收到完整的数据包，最大不超过 package_max_length
 *  3.支持二进制格式，$data 可能是二进制数据
 *
 * @link https://wiki.swoole.com/#/server/events?id=onreceive onReceive
 * @link https://wiki.swoole.com/#/learn?id=tcp%e7%b2%98%e5%8c%85%e9%97%ae%e9%a2%98 TCP 粘包问题
 * @link https://wiki.swoole.com/#/server/port 多端口监听
 */
class Receive
{
    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var int 连接的文件描述符
     */
    public $fd;

    /**
     * @var int TCP 连接所在的 Reactor 线程 ID
     */
    public $reactorId;

    /**
     * @var string 收到的数据内容，可能是文本或者二进制内容
     */
    public $data;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId TCP 连接所在的 Reactor 线程 ID
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     */
    public function __construct($server, $fd, $reactorId, $data)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
        $this->data = $data;
    }
}