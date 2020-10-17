<?php


namespace thinkEasy\facade;


use think\Facade;

/**
 * Class Component
 * @package thinkEasy\facade
 * @method string redirect($url) static 跳转重定向
 */
class Component extends Facade
{
    protected static function getFacadeClass()
    {
        return \thinkEasy\component\Component::class;
    }
}
