<?php

namespace Flysion\Swoolaravel\Events;

/**
 * \Swoole\Http\Request 说明：
 *  1.请勿使用 & 符号引用 Http\Request 对象
 *  2.为防止 HASH 攻击，GET 参数最大不允许超过 128 个
 *  3.POST 与 Header 加起来的尺寸不得超过 package_max_length 的设置，否则会认为是恶意请求
 *  4.POST 参数的个数最大不超过 128 个
 *  5.最大文件尺寸不得超过 package_max_length 设置的值。请勿使用 Swoole\Http\Server 处理大文件上传。
 *  6.当 $request 对象销毁时，会自动删除上传的临时文件
 *
 * \Swoole\Http\Response 说明：
 *  1.请勿使用 & 符号引用 Http\Response 对象
 *  2.当 Response 对象销毁时，如果未调用 end 发送 HTTP 响应，底层会自动执行 end("");
 *  3.header 设置必须在 end 方法之前 -$key 必须完全符合 HTTP 的约定，每个单词首字母大写，不得包含中文，下划线或者其他特殊字符
 *  4.
 *
 * @link https://wiki.swoole.com/#/http_server?id=on onRequest
 * @link https://wiki.swoole.com/#/http_server?id=httprequest \Swoole\Http\Request
 * @link https://wiki.swoole.com/#/http_server?id=httpresponse \Swoole\Http\Response
 */
class Request implements SwooleEvent
{
    /**
     * 事件触发之前
     */
    const before = self::class . ':before';

    /**
     * 事件触发之后
     */
    const after = self::class . ':after';

    /**
     * swoole 事件名称
     */
    const name = 'request';

    /**
     * @var \Swoole\Http\Request
     */
    public $request;

    /**
     * @var \Swoole\Http\Request
     */
    public $response;

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}