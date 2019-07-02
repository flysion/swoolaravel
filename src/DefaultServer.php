<?php namespace Lee2son\Laravoole;

trait DefaultServer {
    public function __construct()
    {
        $config = config('webserver');
        parent::__construct($config['host'], $config['port'], $config['settings'], $config['process_mode'], $config['sock_type']);
    }
}