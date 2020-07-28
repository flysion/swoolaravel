<?php namespace Lee2son\Swoolaravel\Swoole;

use Illuminate\Support\Str;
use Lee2son\Swoolaravel\Events\OnAfterReload;
use Lee2son\Swoolaravel\Events\OnBeforeReload;
use Lee2son\Swoolaravel\Events\OnClose;
use Lee2son\Swoolaravel\Events\OnConnect;
use Lee2son\Swoolaravel\Events\OnFinish;
use Lee2son\Swoolaravel\Events\OnManagerStart;
use Lee2son\Swoolaravel\Events\OnManagerStop;
use Lee2son\Swoolaravel\Events\OnPacket;
use Lee2son\Swoolaravel\Events\OnPipeMessage;
use Lee2son\Swoolaravel\Events\OnReceive;
use Lee2son\Swoolaravel\Events\OnShutdown;
use Lee2son\Swoolaravel\Events\OnStart;
use Lee2son\Swoolaravel\Events\OnTask;
use Lee2son\Swoolaravel\Events\OnTaskCoroutine;
use Lee2son\Swoolaravel\Events\OnWorkerError;
use Lee2son\Swoolaravel\Events\OnWorkerExit;
use Lee2son\Swoolaravel\Events\OnWorkerStart;
use Lee2son\Swoolaravel\Events\OnWorkerStop;
use Lee2son\Swoolaravel\Worker;

/**
 * 事件执行顺序：
 *  1.所有事件回调均在 $server->start 后发生
 *  2.服务器关闭程序终止时最后一次事件是 onShutdown
 *  3.服务器启动成功后，onStart/onManagerStart/onWorkerStart 会在不同的进程内并发执行
 *  4.onReceive/onConnect/onClose 在 Worker 进程中触发
 *  5.Worker/Task 进程启动 / 结束时会分别调用一次 onWorkerStart/onWorkerStop
 *  6.onTask 事件仅在 task 进程中发生
 *  7.onFinish 事件仅在 worker 进程中发生
 *  8.onStart/onManagerStart/onWorkerStart 3 个事件的执行顺序是不确定的
 *
 * @link https://wiki.swoole.com/#/server/tcp_init
 * @mixin \Swoole\Server
 * @property \Illuminate\Contracts\Events\Dispatcher $event
 */
trait Server
{
    public function listen($event, $callback)
    {
        $method = Str::camel("on_{$event}");
        $eventName = "\\Lee2son\\Swoolaravel\\Events\\" . ucfirst($method);
        $this->event->listen($eventName,$callback);
    }

    /**
     * 启动后在主进程（master）的主线程回调此函数
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
     * @param \Swoole\Server $server
     */
    public function onStart($server)
    {
        $this->event->dispatch(new OnStart($server));
    }

    /**
     * 此事件在 Server 正常结束时发生
     *
     * 在此之前 Swoole\Server 已进行了如下操作:
     *  1.已关闭所有 Reactor 线程、HeartbeatCheck 线程、UdpRecv 线程
     *  2.已关闭所有 Worker 进程、 Task 进程、User 进程
     *  3.已 close 所有 TCP/UDP/UnixSocket 监听端口
     *  4.已关闭主 Reactor
     *
     * 请勿在 onShutdown 中调用任何异步或协程相关 API，触发 onShutdown 时底层已销毁了所有事件循环设施。
     *
     * @link https://wiki.swoole.com/#/server/events?id=onshutdown onShutdown
     * @param \Swoole\Server $server
     */
    public function onShutdown($server)
    {
        $this->event->dispatch(new OnShutdown($server));
    }

    /**
     * 此事件在 Worker 进程 / Task 进程启动时发生，这里创建的对象可以在进程生命周期内使用。
     * onWorkerStart/onStart 是并发执行的，没有先后顺序
     * 可以通过 $server->taskworker 属性来判断当前是 Worker 进程还是 Task 进程
     * 设置了 worker_num 和 task_worker_num 超过 1 时，每个进程都会触发一次 onWorkerStart 事件，可通过判断 $worker_id 区分不同的工作进程
     * 由 worker 进程向 task 进程发送任务，task 进程处理完全部任务之后通过 onFinish 回调函数通知 worker 进程。例如:
     * 我们在后台操作向十万个用户群发通知邮件，操作完成后操作的状态显示为发送中，这时我们可以继续其他操作，等邮件群发完毕后，操作的状态自动改为已发送。
     *
     * @link https://wiki.swoole.com/#/server/events?id=onworkerstart onWorkerStart
     * @param \Swoole\Server $server
     * @param int $workerId Worker 进程 id（非进程的 PID）
     * @throws
     */
    public function onWorkerStart($server, $workerId)
    {
        app()->alias(Worker::class, 'swoolaravel.worker');
        app()->singleton(Worker::class, function() use($workerId) {
            return new Worker($workerId);
        });

        $this->event->dispatch(new OnWorkerStart($server, $workerId));
    }

    /**
     * 此事件在 Worker 进程终止时发生。在此函数中可以回收 Worker 进程申请的各类资源。
     *
     * 注意：
     *  1.进程异常结束，如被强制 kill、致命错误、core dump 时无法执行 onWorkerStop 回调函数。
     *  2.请勿在 onWorkerStop 中调用任何异步或协程相关 API，触发 onWorkerStop 时底层已销毁了所有事件循环设施。
     *
     * @link https://wiki.swoole.com/#/server/events?id=onworkerstop onWorkerStop
     * @param \Swoole\Server $server
     * @param int $workerId Worker 进程 id（非进程的 PID）
     */
    public function onWorkerStop($server, $workerId)
    {
        $this->event->dispatch(new OnWorkerStop($server, $workerId));
    }

    /**
     * 仅在开启 reload_async 特性后有效。参见 如何正确的重启服务
     *
     * @link https://wiki.swoole.com/#/server/events?id=onworkerexit onWorkerExit
     * @link https://wiki.swoole.com/#/question/use?id=swoole%e5%a6%82%e4%bd%95%e6%ad%a3%e7%a1%ae%e7%9a%84%e9%87%8d%e5%90%af%e6%9c%8d%e5%8a%a1 如何正确的重启服务
     * @param \Swoole\Server $server
     * @param int $workerId Worker 进程 id（非进程的 PID）
     */
    public function onWorkerExit($server, $workerId)
    {
        $this->event->dispatch(new OnWorkerExit($server, $workerId));
    }

    /**
     * 有新的连接进入时，在 worker 进程中回调。
     *
     * dispatch_mode = 1/3模式下：
     *  1.在此模式下 onConnect/onReceive/onClose 可能会被投递到不同的进程。连接相关的 PHP 对象数据，无法实现在 onConnect 回调初始化数据，onClose 清理数据
     *  2.onConnect/onReceive/onClose 3 种事件可能会并发执行，可能会带来异常
     *
     * 注意：
     *  1.onConnect/onClose 这 2 个回调发生在 worker 进程内，而不是主进程。
     *  2.UDP 协议下只有 onReceive 事件，没有 onConnect/onClose 事件
     *
     * @link https://wiki.swoole.com/#/server/events?id=onconnect onConnect
     * @param \Swoole\Server $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 连接所在的 Reactor 线程 ID
     */
    public function onConnect($server, $fd, $reactorId)
    {
        $this->event->dispatch(new OnConnect($server, $fd, $reactorId));
    }

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
     * @param \Swoole\Server $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId TCP 连接所在的 Reactor 线程 ID
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     */
    public function onReceive($server, $fd, $reactorId, $data)
    {
        $this->event->dispatch(new OnReceive($server, $fd, $reactorId, $data));
    }

    /**
     * 接收到 UDP 数据包时回调此函数，发生在 worker 进程中。
     *
     * 注意：
     *  1.服务器同时监听 TCP/UDP 端口时，收到 TCP 协议的数据会回调 onReceive，收到 UDP 数据包回调 onPacket。
     *    服务器设置的 EOF 或 Length 等自动协议处理 (参考 TCP 粘包问题)，对 UDP 端口是无效的，因为 UDP 包本身存在消息边界，不需要额外的协议处理。
     *
     * @link https://wiki.swoole.com/#/server/events?id=onreceive onPacket
     * @link https://wiki.swoole.com/#/start/start_udp_server UDP 服务器
     * @param \Swoole\Server $server
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     * @param array $clientInfo 客户端信息包括 address/port/server_socket 等多项客户端信息数据，参考 UDP 服务器
     */
    public function onPacket($server, $data, $clientInfo)
    {
        $this->event->dispatch(new OnPacket($server,  $data, $clientInfo));
    }

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
     * @param \Swoole\Server $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 来自那个 reactor 线程，主动 close 关闭时为负数
     */
    public function onClose($server, $fd, $reactorId)
    {
        $this->event->dispatch(new OnClose($server, $fd, $reactorId));
    }

    /**
     * 在 task 进程内被调用。worker 进程可以使用 task 函数向 task_worker 进程投递新的任务。
     * 当前的 Task 进程在调用 onTask 回调函数时会将进程状态切换为忙碌，这时将不再接收新的 Task，当 onTask 函数返回时会将进程状态切换为空闲然后继续接收新的 Task。
     *
     * V4.2.12 起如果开启了 task_enable_coroutine 则回调函数原型是: function (\Swoole\Server $server, \Swoole\Server\Task $task)
     *
     * 返回执行结果到 worker 进程：
     *  1.在 onTask 函数中 return 字符串，表示将此内容返回给 worker 进程。
     *    worker 进程中会触发 onFinish 函数，表示投递的 task 已完成，当然你也可以通过 \Swoole\Server->finish() 来触发 onFinish 函数，而无需再 return
     *  2.return 的变量可以是任意非 null 的 PHP 变量
     *
     * @link https://wiki.swoole.com/#/server/events?id=ontask onTask
     * @see Server::onTaskCoroutine()
     * @param \Swoole\Server $server
     * @param int $taskId 执行任务的 task 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     * @param int $srcWorkerId 投递任务的 worker 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     * @param mixed $data 是任务的数据内容
     */
    public function onTask($server, $taskId, $srcWorkerId, $data)
    {
        $this->event->dispatch(new OnTask($server, $taskId, $srcWorkerId, $data));
    }

    /**
     * V4.2.12 起如果开启了 task_enable_coroutine 这 onTask 事件响应该方法
     *
     * @see Server::onTask()
     * @param \Swoole\Server $server
     * @param \Swoole\Server\Task $task
     */
    public function onTaskCoroutine($server, $task)
    {
        $this->event->dispatch(new OnTaskCoroutine($server, $task));
    }

    /**
     * 此回调函数在 worker 进程被调用，当 worker 进程投递的任务在 task 进程中完成时， task 进程会通过 Swoole\Server->finish() 方法将任务处理的结果发送给 worker 进程。
     *
     * 注意：
     *  1.task 进程的 onTask 事件中没有调用 finish 方法或者 return 结果，worker 进程不会触发 onFinish
     *  2.执行 onFinish 逻辑的 worker 进程与下发 task 任务的 worker 进程是同一个进程
     *
     * @link https://wiki.swoole.com/#/server/events?id=onfinish onFinish
     * @param \Swoole\Server $server
     * @param int $taskId 执行任务的 task 进程 id
     * @param mixed $data 任务处理的结果内容
     */
    public function onFinish($server, $taskId, $data)
    {
        $this->event->dispatch(new OnFinish($server, $taskId, $data));
    }

    /**
     * 当工作进程收到由 $server->sendMessage() 发送的 unixSocket 消息时会触发 onPipeMessage 事件。worker/task 进程都可能会触发 onPipeMessage 事件
     *
     * @link https://wiki.swoole.com/#/server/events?id=onpipemessage onPipeMessage
     * @param \Swoole\Server $server
     * @param int $srcWorkerId 消息来自哪个 Worker 进程
     * @param mixed $message 消息内容，可以是任意 PHP 类型
     */
    public function onPipeMessage($server, $srcWorkerId, $message)
    {
        $this->event->dispatch(new OnPipeMessage($server, $srcWorkerId, $message));
    }

    /**
     * 当 Worker/Task 进程发生异常后会在 Manager 进程内回调此函数。
     *
     * 此函数主要用于报警和监控，一旦发现 Worker 进程异常退出，那么很有可能是遇到了致命错误或者进程 CoreDump。通过记录日志或者发送报警的信息来提示开发者进行相应的处理。
     *
     * 常见错误：
     *  1.signal = 11：说明 Worker 进程发生了 segment fault 段错误，可能触发了底层的 BUG，请收集 core dump 信息和 valgrind 内存检测日志，向我们反馈此问题
     *  2.exit_code = 255：说明 Worker 进程发生了 Fatal Error 致命错误，请检查 PHP 的错误日志，找到存在问题的 PHP 代码，进行解决
     *  3.signal = 9：说明 Worker 被系统强行 Kill，请检查是否有人为的 kill -9 操作，检查 dmesg 信息中是否存在 OOM（Out of memory）
     *  4.如果存在 OOM，分配了过大的内存。是否创建了非常大的 \Swoole\Table 内存模块。
     *
     * @link https://wiki.swoole.com/#/server/events?id=onworkererror onWorkerError
     * @param \Swoole\Server $server
     * @param int $workerId 异常 worker 进程的 id
     * @param int $workerPid 异常 worker 进程的 pid
     * @param int $exitCode 退出的状态码，范围是 0～255
     * @param int $signal 进程退出的信号
     */
    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal)
    {
        $this->event->dispatch(new OnWorkerError($server, $workerId, $workerPid, $exitCode, $signal));
    }

    /**
     * 当管理进程启动时触发此事件
     * 在这个回调函数中可以修改管理进程的名称。
     * 在 4.2.12 以前的版本中 manager 进程中不能添加定时器，不能投递 task 任务、不能用协程。
     * 在 4.2.12 或更高版本中 manager 进程可以使用基于信号实现的同步模式定时器
     * manager 进程中可以调用 sendMessage 接口向其他工作进程发送消息
     *
     * 启动顺序：
     *  1.Task 和 Worker 进程已创建
     *  2.Master 进程状态不明，因为 Manager 与 Master 是并行的，onManagerStart 回调发生是不能确定 Master 进程是否已就绪
     *
     * BASE 模式：
     *  1.在 SWOOLE_BASE 模式下，如果设置了 worker_num、max_request、task_worker_num 参数，底层将创建 manager 进程来管理工作进程。因此会触发 onManagerStart 和 onManagerStop 事件回调。
     *
     * @link https://wiki.swoole.com/#/server/events?id=onmanagerstart onManagerStart
     * @param \Swoole\Server $server
     */
    public function onManagerStart($server)
    {
        $this->event->dispatch(new OnManagerStart($server));
    }

    /**
     * 当管理进程结束时触发
     * onManagerStop 触发时，说明 Task 和 Worker 进程已结束运行，已被 Manager 进程回收。
     *
     * @link https://wiki.swoole.com/#/server/events?id=onmanagerstop
     * @param \Swoole\Server $server
     */
    public function onManagerStop($server)
    {
        $this->event->dispatch(new OnManagerStop($server));
    }

    /**
     * Worker 进程 Reload 之前触发此事件，在 Manager 进程中回调
     *
     * @param \Swoole\Server $server
     */
    public function onBeforeReload($server)
    {
        $this->event->dispatch(new OnBeforeReload($server));
    }

    /**
     * Worker 进程 Reload 之后触发此事件，在 Manager 进程中回调
     *
     * @param \Swoole\Server $server
     */
    public function onAfterReload($server)
    {
        $this->event->dispatch(new OnAfterReload($server));
    }
}