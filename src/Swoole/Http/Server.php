<?php namespace Lee2son\Laravoole\Swoole\Http;

use Lee2son\Laravoole\Swoole\EventRewriteable;
use Lee2son\Laravoole\Swoole\RewriteRequest;
use Lee2son\Laravoole\Swoole\RewriteWorkerStart;
use Lee2son\Laravoole\Swoole\Worker;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Server extends \Swoole\Http\Server
{
    use EventRewriteable, RewriteRequest, RewriteWorkerStart;
}