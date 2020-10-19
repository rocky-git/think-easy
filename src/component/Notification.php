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
 * 通知
 * Class Notification
 * @package thinkEasy\component
 */
class Notification
{
    /**
     * 成功提示
     * @param $title 标题
     * @param $message 提示信息
     * @param string $url 跳转url
     */
    public function success($title,$message, $url = '')
    {
        $this->response($title,$message, 'success', $url);
    }
    /**
     * 警告提示
     * @param $title 标题
     * @param $message 提示信息
     * @param string $url 跳转url
     */
    public function warning($title,$message, $url = '')
    {
        $this->response($title,$message, 'warning', $url);
    }
    /**
     * 信息提示
     * @param $title 标题
     * @param $message 提示信息
     * @param string $url 跳转url
     */
    public function info($title,$message, $url = '')
    {
        $this->response($title,$message, 'info', $url);
    }
    /**
     * 错误提示
     * @param $title 标题
     * @param $message 提示信息
     * @param string $url 跳转url
     */
    public function error($title,$message, $url = '')
    {
        $this->response($title,$message, 'error', $url);
    }
    protected function response($title,$message, $type, $url = '')
    {
        throw new HttpResponseException(json([
            'code' => 80021,
            'type' => $type,
            'title'=>$title,
            'message'=>$message,
            'url' => $url
        ]));
    }
}