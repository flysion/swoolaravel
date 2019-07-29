<?php namespace Lee2son\Swoolaravel\Server;

use Lee2son\Swoolaravel\Swoole\WebSocket\Server;
use Swoole\Table as SwooleTable;
use Swoole\WebSocket\Server as SwooleWebsocketServer;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Frame as SwooleWebSocketFrame;
use Illuminate\Http\Request;

class WebSocketServer extends Server implements \Lee2son\Swoolaravel\Server
{

}