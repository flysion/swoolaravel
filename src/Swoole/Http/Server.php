<?php

namespace Lee2son\Swoolaravel\Swoole\Http;

/**
 * 注意：
 *  1.Http\Server 对 HTTP 协议的支持并不完整，一定要作为应用服务器处理动态请求。并且在前端增加 Nginx 作为代理
 *  2.不接受 onConnect/onReceive 回调设置
 *  3.额外接受 1 种新的事件类型 onRequest
 *
 * @link https://wiki.swoole.com/#/http_server
 * @mixin \Swoole\Http\Server
 */
class Server extends \Lee2son\Swoolaravel\Swoole\Server
{
    /**
     * @param \Illuminate\Config\Repository $config
     * @return \Swoole\Http\Server
     */
    protected static function createSwooleServer($config)
    {
        return new \Swoole\Http\Server(
            $config->get('host') ?: '0.0.0.0',
            $config->get('port') ?? 0
        );
    }

    /**
     * swoole 默认设置，优先级最高，无法被其他设置覆盖
     *
     * @return array
     */
    protected function defaultSetting()
    {
        return array_merge(parent::defaultSetting(), [
            'open_http_protocol' => true,
        ]);
    }
}