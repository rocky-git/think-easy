<?php

namespace thinkEasy\component;

use think\exception\HttpResponseException;

/**
 * 前端组件类
 * Class Component
 * @package thinkEasy\component
 */
class Component
{
    /**
     * 跳转重定向
     * @param $url 跳转链接
     */
    public function redirect($url)
    {
        throw new HttpResponseException(json([
            'code' => 40021,
            'url' => $url
        ]));
    }
}
