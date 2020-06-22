<?php


namespace thinkEasy\form;
/**
 * 图标选择器
 * Class Icon
 * @package thinkEasy\form
 */
class Icon extends Field
{
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<e-icon-picker {$attrStr} />";
        return $html;
    }
}
