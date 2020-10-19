<?php


namespace thinkEasy\facade;


use think\Facade;

/**
 * @see \thinkEasy\component\Component
 * Class Component
 * @package thinkEasy\facade
 * @method string redirect($url,$params) static 跳转重定向
 * @method string view($template,$vars=[],$props=[]) static 跳转重定向
 */
class Component extends Facade
{
    protected static function getFacadeClass()
    {
        return \thinkEasy\component\Component::class;
    }
}
