<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-09
 * Time: 21:17
 */
namespace thinkEasy\facade;
use think\Facade;

/**
 * Class Button
 * @package \thinkEasy\form\Button
 * @method \thinkEasy\form\Button create($text='',$colorType='',$size='medium',$icon='',$plain=false) static 创建按钮
 */
class Button extends Facade
{
    protected static function getFacadeClass()
    {
        return \thinkEasy\form\Button::class;
    }
}
