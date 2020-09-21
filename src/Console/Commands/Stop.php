<?php
namespace Lee2son\Swoolaravel\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Stop extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoolaravel:stop';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = '停止服务';

    /**
     * command handle
     */
    public function handle()
    {

    }
}