<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-10-19
 * Time: 22:01
 */

namespace thinkEasy\component;


use think\exception\HttpResponseException;

/**
 * 消息提示
 * Class Message
 * @package thinkEasy\component
 */
class Message
{
    /**
     * 成功提示
     * @param $message 提示信息
     * @param string $url 跳转url
     */
    public function success($message, $url = '')
    {
        $this->response($message, 'success', $url);
    }
    /**
     * 警告提示
     * @param $message 提示信息
     * @param string $url 跳转url
     */
    public function warning($message, $url = '')
    {
        $this->response($message, 'warning', $url);
    }
    /**
     * 信息提示
     * @param $message 提示信息
     * @param string $url 跳转url
     */
    public function info($message, $url = '')
    {
        $this->response($message, 'info', $url);
    }
    /**
     * 错误提示
     * @param $message 提示信息
     * @param string $url 跳转url
     */
    public function error($message, $url = '')
    {
        $this->response($message, 'error', $url);
    }
    protected function response($message, $type, $url = '')
    {
        throw new HttpResponseException(json([
            'code' => 80020,
            'type' => $type,
            'message'=>$message,
            'url' => $url
        ]));
    }
}