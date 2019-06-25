<?php
namespace Lee2son\Laravoole\Console\Commands;

use Illuminate\Console\Command;

class Service extends Command
{
    protected $signature = 'laravoole:service {type}';

    protected $description = '服务管理';

    public function handle()
    {
        $server = app('laravoole.server');
        $server->start();
    }
}