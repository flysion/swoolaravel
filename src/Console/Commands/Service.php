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

        $method = 'on' . ucfirst($this->argument('type'));
        if(method_exists($this, $method)) {
            $this->$method($server);
        }
    }

    public function onStart($server)
    {
        $server->start();
    }
}