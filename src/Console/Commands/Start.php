<?php
namespace Lee2son\Swoolaravel\Console\Commands;

class Start extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoolaravel:start {serverName}';

    /**
     * command handle
     */
    public function handle()
    {
        app($this->argument('serverName'))->start();
    }
}