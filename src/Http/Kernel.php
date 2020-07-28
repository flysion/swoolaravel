<?php namespace Lee2son\Swoolaravel\Http;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;

class Kernel extends \App\Http\Kernel
{
    public function __construct(Application $app, Router $router)
    {
        $_ENV['APP_RUNNING_IN_CONSOLE'] = 'false';
        parent::__construct($app, $router);
    }
}