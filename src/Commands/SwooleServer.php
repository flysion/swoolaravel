<?php
namespace Lee2son\Laravoole\Commands;

use Illuminate\Console\Command;

class Service extends Command
{
    protected $signature = 'swoole_server:service {type}}';

    protected $description = 'swoole_server的服务管理';

    public function handle()
    {

    }
}