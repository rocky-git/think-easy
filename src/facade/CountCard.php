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
 * Class CountCard
 * @package \thinkEasy\layout\CountCard
 * @method \thinkEasy\layout\CountCard create($title,$currentTotal,$total,$icon,$iconColor='',$badge='日') static 创建统计卡片
 * @method \thinkEasy\layout\CountCard href($url) static 跳转链接
 */
class CountCard extends Facade
{
    protected static function getFacadeClass()
    {
        return \thinkEasy\layout\CountCard::class;
    }
}