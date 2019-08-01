<?php
namespace Lee2son\Swoolaravel\Console\Commands;

use Illuminate\Console\Command;

class Server extends Command
{
    protected $signature = 'swoolaravel:server {serverName} {type}';

    protected $description = '服务管理';

    public function handle()
    {
        $serverName = $this->argument('serverName');
        $type = $this->argument('type');

        $method = 'on' . ucfirst($type);
        if(method_exists($this, $method)) {
            $this->$method($serverName);
        }
    }

    public function onStart($serverName)
    {
        $server = app($serverName);
        $server->start();
    }
}