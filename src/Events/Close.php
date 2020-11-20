<?php

namespace Flysion\Swoolaravel\Events;

/**
 * TCP 客户端连接关闭后，在 worker 进程中回调此函数。
 *
 * 主动关闭：
 *  1.当服务器主动关闭连接时，底层会设置此参数为 -1，可以通过判断 $reactorId < 0 来分辨关闭是由服务器端还是客户端发起的。
 *  2.只有在 PHP 代码中主动调用 close 方法被视为主动关闭
 *
 * 心跳监测：
 *  1.心跳检测是由心跳检测线程通知关闭的，关闭时 onClose 的 $reactorId 参数不为 -1
 *
 * 注意：
 *  1.onClose 回调函数如果发生了致命错误，会导致连接泄漏。通过 netstat 命令会看到大量 CLOSE_WAIT 状态的 TCP 连接 ，参考 Swoole 视频教程
 *  2.无论由客户端发起 close 还是服务器端主动调用 $server->close() 关闭连接，都会触发此事件。因此只要连接关闭，就一定会回调此函数
 *  3.onClose 中依然可以调用 getClientInfo 方法获取到连接信息，在 onClose 回调函数执行完毕后才会调用 close 关闭 TCP 连接
 *  4. 这里回调 onClose 时表示客户端连接已经关闭，所以无需执行 $server->close($fd)。代码中执行 $server->close($fd) 会抛出 PHP 错误警告。
 *
 * @link https://wiki.swoole.com/#/server/events?id=onclose onClose
 * @link https://wiki.swoole.com/#/server/setting?id=heartbeat_check_interval 心跳检测
 */
class Close implements SwooleEvent
{
    /**
     * 事件触发之前
     */
    const before = self::class . ':before';

    /**
     * 事件触发之后
     */
    const after = self::class . ':after';

    /**
     * swoole 事件名称
     */
    const name = 'close';

    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @var int 连接的文件描述符
     */
    public $fd;

    /**
     * @var int 来自那个 reactor 线程，主动 close 关闭时为负数
     */
    public $reactorId;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 来自那个 reactor 线程，主动 close 关闭时为负数
     */
    public function __construct($server, $fd, $reactorId)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }
}