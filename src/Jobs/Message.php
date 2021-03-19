<?php

namespace Flysion\Swoolaravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * 将队列转发到 pipeMessage
 * 最有用的作用就是进程间通信和跨节点通信：用户->HTTP->QUEUE->pipeMessage
 * 通过为每一个 server 建立专用的队列可实现 server 间通信
 *
 * @see \Illuminate\Queue\Worker::daemon() 队列执行逻辑
 * @see \Illuminate\Queue\Worker::process() 作业执行逻辑
 * @see \Illuminate\Queue\Queue::createObjectPayload() 投递到队列的过程
 * @see \Illuminate\Queue\Queue::getDisplayName() 自定义作业显示名称
 * @see \Illuminate\Queue\Queue::getJobRetryDelay() 自定义失败（抛出异常）重试延迟时间
 * @see \Illuminate\Queue\Queue::getJobExpiration() 自定义作业过期时间
 * @see \Illuminate\Bus\Dispatcher::dispatch()
 * @see \Illuminate\Bus\Dispatcher::dispatchToQueue()
 */
class Message implements ShouldQueue/* 在队列中执行 */
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 重试次数【在作业初次加入队列时有效】
     * 优先级比 --tries 高
     * 如果是通过 release 来重试的，队列总共会执行 tries+1 次
     *
     * 哪些情况下会重试：
     * 1.调用了 release 方法
     * 2.执行过程中抛出了异常
     * 3.执行时间超过了作业执行超时时间
     *
     * @see \Illuminate\Queue\Queue::createObjectPayload()
     * @var int|null
     */
    public $tries = null;

    /**
     * 抛出异常的最大次数【在作业初次加入队列时有效】
     *
     * @see \Illuminate\Queue\Queue::createObjectPayload()
     * @var int|null
     */
    public $maxExceptions = null;

    /**
     * 作业失败重试间隔【在作业初次加入队列时有效】
     *
     * 与 $this->retryAfter() 具有相同的效果，优先级：
     * $this->retryAfter > $this->retryAfter()
     *
     * @see \Illuminate\Queue\Queue::getJobRetryDelay()
     * @var null|int
     */
    public $retryAfter = null;

    /**
     * 作业执行超时时长【在作业初次加入队列时有效】
     * 单位：秒
     *
     * @see \Illuminate\Queue\Queue::createObjectPayload()
     * @var null|int
     */
    public $timeout = null;

    /**
     * 作业过期时间【在作业初次加入队列时有效】
     * 在达到过期时间之后无论如何作业都不会在执行了
     *
     * 与 $this->retryUntil() 具有相同的效果，优先级：
     * $this->timeoutAt > $this->retryUntil()
     *
     * @see \Illuminate\Queue\Queue::getJobExpiration()
     * @var \DateTimeInterface|int
     */
    public $timeoutAt = null;

    /**
     * @var mixed
     */
    protected $message;

    /**
     * @var
     */
    protected $dstWorkerId;

    /**
     * @param mixed $message
     * @param int $workerId
     */
    public function __construct($message, $dstWorkerId)
    {
        $this->message = $message;
        $this->dstWorkerId;
    }

    /**
     * 作业失败之后调用
     *
     * 失败的定义：
     * 1.重试次数超过了 tries 次数
     * 2.主动调用了 $this->fail() 方法
     * 3.抛出异常的次数操过了 $this->maxExceptions 次数
     * 4.作业过期（$this->timeoutAt）
     *
     * @param \Illuminate\Queue\MaxAttemptsExceededException|null $exception
     */
    public function failed($exception = null)
    {

    }

    /**
     * 作业失败重试间隔【在作业初次加入队列时有效】
     *
     * 与 $this->retryAfter 具有相同的效果，优先级：
     * $this->retryAfter > $this->retryAfter()
     *
     * @see \Illuminate\Queue\Queue::getJobRetryDelay()
     * @return int
     */
    public function retryAfter()
    {

    }

    /**
     * 作业过期时间【在作业初次加入队列时有效】
     * 在达到过期时间之后无论如何作业都不会在执行了
     *
     * 与 $this->timeoutAt 具有相同的效果，优先级：
     * $this->timeoutAt > $this->retryUntil()
     *
     * @see \Illuminate\Queue\Queue::getJobExpiration()
     * @var \DateTimeInterface|int
     */
    public function retryUntil()
    {

    }

    /**
     * 作业处理方法
     */
    public function handle()
    {
        app('server')->sendMessage($this->message, $this->dstWorkerId);
    }
}