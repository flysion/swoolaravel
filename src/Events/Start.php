<?php

namespace Flysion\Swoolaravel\Events;

/**
 * 启动后在主进程（master）的主线程回调此函数【主进程】
 *
 * 在此事件之前 Server 已进行了如下操作:
 *  1.启动创建完成 manager 进程
 *  2.启动创建完成 worker 子进程
 *  3.监听所有 TCP/UDP/unixSocket 端口，但未开始 Accept 连接和请求
 *  4.监听了定时器
 *
 * 接下来要执行:
 *  1.主 Reactor 开始接收事件，客户端可以 connect 到 Server
 *
 * onStart 回调中，仅允许 echo、打印 Log、修改进程名称。不得执行其他操作不能调用 server 相关函数等操作，因为服务尚未就绪)。
 * onWorkerStart 和 onStart 回调是在不同进程中并行执行的，不存在先后顺序。
 *
 * @link https://wiki.swoole.com/#/server/events?id=onstart onStart
 */
class Start implements SwooleEvent
{
    /**
     * @var \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server
     */
    public $server;

    /**
     * @param \Swoole\Server|\Swoole\Http\Server|\Swoole\WebSocket\Server $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}