<?php namespace Lee2son\Laravoole\Http\WebSocket;

class Server extends \Lee2son\Laravoole\Http\Server {

    const SWOOLE_SERVER = \Swoole\WebSocket\Server::class;

    /**
     * @var \Swoole\Table|null
     */
    public $clients = null;

    /**
     * Server constructor
     */
    public function __construct()
    {
        $config = config('webserver');
        if(count($config['client_table']['columns']) > 0) {
            $this->clients = new \Swoole\Table($config['client_table']['max_size']);
            foreach($config['client_table']['columns'] as $field => $type) {
                $this->clients->column($field, $type);
            }
            $this->clients->create();
        }

        parent::__construct();

        if($this->clients !== null) {
            $this->on('Open');
            $this->on('Close');
        }
    }

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数 see https://wiki.swoole.com/wiki/page/401.html
     * @param Server $server
     * @param \Swoole\Http\Request $req
     * @return false|[\Swoole\Websocket\Server $server, \Illuminate\Http\Request $request, int $fd, array $client]
     */
    protected function onOpen($server, \Swoole\Http\Request $req)
    {
        $request = swoole_request_to_laravel_request($req);

        if(!$this->checkOrigin($request, $req->fd)) {
            $server->close($req->fd);
            return false;
        }

        if($this->clients !== null) {
            $client = $this->createClient($request, $req->fd);
            if(is_array($client)) $this->clients->set($req->fd, $client);
        }

        return [
            $server,
            $request,
            $req->fd,
            $client ?? null
        ];
    }

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数 see https://wiki.swoole.com/wiki/page/p-event/onClose.html
     * swoole-1.9.7版本修改了$reactorId参数，当服务器主动关闭连接时，底层会设置此参数为-1，可以通过判断$reactorId < 0来分辨关闭是由服务器端还是客户端发起的
     * @param Server $server
     * @param int $fd
     * @param int $reactorId
     * @return [\Swoole\Websocket\Server $server, int $fd, int $reactorId]
     */
    protected function onClose($server, int $fd, int $reactorId)
    {
        if($this->clients !== null) {
            $this->clients->del($fd);
        }

        return [$server, $fd, $reactorId];
    }

    /**
     * 触发 onopen 事件的时候创建一个客户端，需要设置“webserver.client_table”。see https://wiki.swoole.com/wiki/page/257.html
     * @param \Illuminate\Http\Request $request
     * @param int $fd
     * @return array|null 如果返回 null 则该连接不会加入 clients （但不会关闭连接）
     */
    protected function createClient(\Illuminate\Http\Request $request, int $fd)
    {
        return null;
    }

    /**
     * 检查客户端连接源
     * @param \Illuminate\Http\Request $request
     * @param int $fd
     * @return bool 如果返回 false 则连接会被关闭，同时不再触发 onopen 事件
     */
    protected function checkOrigin(\Illuminate\Http\Request $request, int $fd) : bool
    {
        return true;
    }
}