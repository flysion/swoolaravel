<?php
namespace Lee2son\Swoolaravel\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Start extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'swoolaravel:start';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = '启动服务';

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $inputOptions = [];

        foreach(config('server_options') as $optionName => $option)
        {
            $mode = @$option['required'] ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL;

            if(@$option['value_none']) {
                $mode = InputOption::VALUE_NONE;
            }

            $inputOptions[] = new InputOption($optionName, $option['shortname'] ?? null, $mode, $option['desc'] ?? '', $option['default'] ?? null);
        }

        return $inputOptions;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            new InputArgument('serverName', InputArgument::REQUIRED, '服务配置名称，通过 config(serverName) 获取'),
        ];
    }

    /**
     * command handle
     */
    public function handle()
    {
        $serverOptions = $this->buildServerOptions();

        $launcherClass = $serverOptions['launcher'];
        $launcher = new $launcherClass();
        $launcher->start($serverOptions);
    }

    /**
     * 通过命令行参数拼装服务选项
     *
     * @return array
     */
    protected function buildServerOptions()
    {
        $serverName = $this->argument('serverName');
        $config = new \Illuminate\Config\Repository(config($serverName, []));
        $options = config('server_options');

        foreach($this->options() as $key => $value)
        {
            if(!isset($options[$key]))
            {
                continue;
            }

            $option = $options[$key];

            if(!$this->input->hasParameterOption("--{$key}") && (!isset($option['shortname']) || !$this->input->hasParameterOption("-{$option['shortname']}")))
            {
                continue;
            }

            if(isset($option['formatter']))
            {
                $config->set($key, $this->formatOption($option['formatter'], $value));
            } else {
                $config->set($key, $value);
            }
        }

        if($config->get('setting.enable_static_handler', false))
        {
            $config->set('setting.document_root', public_path());
        }

        $config->set('setting.pid_file', storage_path("{$serverName}.pid"));

        return $config->all();
    }

    /**
     * 格式化选项
     *
     * @param callable $formatter
     * @param mixed $value
     * @return mixed string
     */
    protected function formatOption($formatter, $value)
    {
        switch ($formatter)
        {
            case 'storage_path':
                if($value[0] !== '/' && $value[0] !== '.') {
                    return storage_path($value);
                }

                return $value;
            case 'public_path':
                if($value[0] !== '/' && $value[0] !== '.') {
                    return storage_path($value);
                }

                return $value;
            default:
                return $formatter($value);
        }
    }
}