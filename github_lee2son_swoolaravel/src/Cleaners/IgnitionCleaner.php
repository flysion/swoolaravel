<?php

namespace Lee2son\Swoolaravel\Cleaners;

class IgnitionCleaner implements CleanerInterface
{
    /**
     * @param \Lee2son\Swoolaravel\Swoole\Server|\Lee2son\Swoolaravel\Swoole\Http\Server|\Lee2son\Swoolaravel\Swoole\WebSocket\Server $server
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @see \Facade\Ignition\IgnitionServiceProvider::setupQueue()
     */
    public function handle($server)
    {
        app()->get(\Facade\FlareClient\Flare::class)->reset();

        if (config('flare.reporting.report_queries')) {
            app()->make(\Facade\Ignition\QueryRecorder\QueryRecorder::class)->reset();
        }

        app()->make(\Facade\Ignition\LogRecorder\LogRecorder::class)->reset();

        app()->make(\Facade\Ignition\DumpRecorder\DumpRecorder::class)->reset();
    }
}