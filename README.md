# swoole-laravel

## 使用示例
```php
// 步骤1
\Illuminate\Support\Facades\Route::get('/', function(\Illuminate\Http\Request $request) {
    // 朝队列添加一个作业，作业是把消息发送到 pipemessage
    \Flysion\Swoolaravel\Jobs\Message::dispatch("1234567890", 0)->onConnection('redis')->onQueue('test');
    return "Ok";
});

// 步骤2
$server = new class(app('events'), '0.0.0.0', 25001) extends \Flysion\Swoolaravel\Swoole\Http\Server {
    public $processNamePrefix = 'test-';

    public function onReady()
    {
        // 创建一个队列消费进程
        $this->addProcess(new \Flysion\Swoolaravel\Swoole\Process\QueueWorker('redis', 'test', new \Illuminate\Queue\WorkerOptions(), $this->processNamePrefix));
    }
};

$server->on('start', function($server, \Flysion\Swoolaravel\Events\Start $event) {
    echo "hello swoole\n";
});

$server->on('task', function($server, \Flysion\Swoolaravel\Events\Task $event) {
    echo "task: {$event->data}";
});

// 接收来自 pipemessage 的消息
$server->on('pipeMessage', function($server, \Flysion\Swoolaravel\Events\PipeMessage $event) {
    echo "message: {$event->message}";
});

$server->start([
    'worker_num' => 2,
    'task_worker_num' => 2
]);
```