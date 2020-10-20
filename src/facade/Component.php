<?php


namespace thinkEasy\facade;


use think\Facade;

/**
 * Class Component
 * @package thinkEasy\facade
 * @method string redirect($url,$params) static 跳转重定向
 * @method string fetch($template,$props=[],$vars=[]) static 渲染组件模板
 * @method string view($content) static 渲染视图内容
 * @method \thinkEasy\component\Message message() static 消息提示
 * @method \thinkEasy\component\Notification notification() static 通知
 */
class Component extends Facade
{
    protected static function getFacadeClass()
    {
        return \thinkEasy\Component::class;
    }
}
