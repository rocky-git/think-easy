<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-06-22
 * Time: 21:55
 */

namespace thinkEasy\facade;

use think\Facade;

/**
 * Class Quick
 * @package \thinkEasy\layout\Quick
 * @method \thinkEasy\layout\Quick create($title,$icon,$iconColor='') static 创建快捷入口
 * @method \thinkEasy\layout\Quick href($url) static 跳转链接
 */
class Quick extends Facade
{
    protected static function getFacadeClass()
    {
        return \thinkEasy\layout\Quick::class;
    }
}