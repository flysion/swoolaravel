<?php
namespace Lee2son\Laravoole;

use Lee2son\Laravoole\Exceptions\InvalidEventException;
use Swoole\Table as SwooleTable;
use Swoole\WebSocket\Server as SwooleWebSocketServer;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;

class HttpServer {
    /**
     * @var SwooleWebSocketServer
     */
    private $server;

    /**
     * @var SwooleTable
     */
    private $clients;

    /**
     * @var array from laravoole.php
     */
    private $config;

    /**
     * @var callable 在客户端连接上时调用
     */
    private $onOpen;

    /**
     * @var callback 在客户端关闭时调用
     */
    private $onClose;

    /**
     * @var callback 在接收到客户端消息时调用
     */
    private $onMessage;

    public function __construct()
    {
        $this->config = config('laravoole');

        $this->server = new SwooleWebSocketServer($this->config['host'], $this->config['port'], $this->config['process_type']);
        $this->server->set(array_merge($this->config['server_options'], [
            'http_parse_post' => true,
            'http_parse_cookie' => true,
        ]));

        $this->server->on('open', [$this, '_onOpen']);
        $this->server->on('close', [$this, '_onClose']);
        $this->server->on('message', [$this, '_onMessage']);
        $this->server->on('request', [$this, '_onRequest']);

        $this->clients = new SwooleTable($this->config['client_options']['max_size']);
        foreach($this->config['client_options']['columns'] as $column => $type)
        {
            $this->clients->column($column, $type);
        }
        $this->clients->create();
    }

    private function _onOpen(SwooleWebSocketServer $server, SwooleHttpRequest $request)
    {
        // todo check origin

        if(is_callable($this->onOpen))
        {
            $client = $this->onOpen($server, $request->fd);
            if($client !== null) {
                $this->clients->set($request->fd, $client);
            } else {
                $this->server->close($request->fd);
            }
        }
    }

    private function _onMessage(SwooleWebSocketServer $server, $frame)
    {

    }

    private function _onRequest(SwooleHttpRequest $req, SwooleHttpResponse $resp)
    {

    }

    private function _onClose(SwooleWebSocketServer $server, $fd)
    {
        $this->clients->del($fd);
    }

    public function on($event, callable $callback)
    {
        switch ($event) {
            case 'open':
                $this->onOpen = $callback;
                break;
            case 'close':
                $this->onClose = $callback;
                break;
            case 'message':
                $this->onMessage = $callback;
                break;
            default:
                throw new InvalidEventException("\"{$event}\" is invalid");
        }
    }
}