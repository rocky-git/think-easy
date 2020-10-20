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
    protected $data = [];

    /**
     * 成功提示
     * @param $title 标题
     * @param $message 提示信息
     * @param string $url 跳转url
     * @return $this
     */
    public function success($title, $message, $url = '')
    {
        $this->response($title, $message, 'success', $url);
        return $this;
    }

    /**
     * 警告提示
     * @param $title 标题
     * @param $message 提示信息
     * @param string $url 跳转url
     * @return $this
     */
    public function warning($title, $message, $url = '')
    {
        $this->response($title, $message, 'warning', $url);
        return $this;
    }

    /**
     * 信息提示
     * @param $title 标题
     * @param $message 提示信息
     * @param string $url 跳转url
     * @return $this
     */
    public function info($title, $message, $url = '')
    {
        $this->response($title, $message, 'info', $url);
        return $this;
    }

    /**
     * 错误提示
     * @param $title 标题
     * @param $message 提示信息
     * @param string $url 跳转url
     * @return $this
     */
    public function error($title, $message, $url = '')
    {
        $this->response($title, $message, 'error', $url);
        return $this;
    }

    /**
     * 刷新当前页面
     */
    public function refresh()
    {
        $this->data = array_merge($this->data, ['refresh' => true]);
    }
    protected function response($title, $message, $type, $url = '')
    {
        $this->data = [
            'code' => 80020,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url
        ];
    }

    public function __destruct()
    {
        throw new HttpResponseException(json($this->data));
    }
}
