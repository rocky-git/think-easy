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
 * Class TimeLine
 * @package \thinkEasy\layout\TimeLine
 * @method \thinkEasy\layout\TimeLine create(array $data,string $timeField,string $contentField) static 创建
 * @method \thinkEasy\layout\TimeLine asc() static 排序小到大
 */
class TimeLine extends Facade
{
    protected static function getFacadeClass()
    {
        return \thinkEasy\layout\TimeLine::class;
    }
}
