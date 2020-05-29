<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-10
 * Time: 16:14
 */

namespace thinkEasy\form;


/**
 * Switch 开关
 * Class Switchs
 * @package thinkEasy\form
 */
class Switchs extends Field
{
    public function __construct($field, $label, $arguments = [])
    {
        parent::__construct($field, $label, $arguments);
        $this->state([1 => '开启'], [0 => '关闭']);

    }

    /**
     * 设置状态
     * @param array $active 开启状态 [1=>'开启']
     * @param array $inactive 关闭状态 [0=>'关闭]
     * @param string $color 颜色
     */
    public function state(array $active, array $inactive, $color = '#409EFF')
    {
        $activeText = current($active);
        $inactiveText = current($inactive);
        $this->setAttr('active-text', current($active));
        $this->setAttr(':active-value', key($active));
        $this->setAttr('inactive-text', current($inactive));
        $this->setAttr(':inactive-value', key($inactive));
        $this->setAttr('active-color', $color);
        $this->setAttr(':width', 75);
    }

    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<eadmin-switch {$attrStr}></eadmin-switch>";
        return $html;
    }
}