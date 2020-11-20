<?php
namespace Flysion\Swoolaravel\Console\Commands;

use Illuminate\Support\Str;

class Server extends \Illuminate\Console\Command
{
    protected $signature = 'swoolaravel:server {server} {type}';

    protected $description = '服务管理';

    public function handle()
    {
        $server = $this->argument('server');
        $type = $this->argument('type');

        $method = '_' . Str::camel($type);
        if(method_exists($this, $method)) {
            $this->$method($server);
        }
    }

    protected function _start($server)
    {
        $server = app($server);
        $server->start();
    }
}