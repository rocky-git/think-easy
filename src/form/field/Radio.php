<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-19
 * Time: 21:50
 */

namespace thinkEasy\form\field;

use thinkEasy\form\Field;
class Radio extends Field
{
    protected $attrs = [
        'data',
        'disabled',
    ];
    protected $optionHtml = '<el-radio %s>{{item.label}}</el-radio>';
    protected $eventJs = null;
    /**
     * 设置选项数据
     * @param array $datas
     */
    public function options(array $datas)
    {
        $options = [];
        foreach ($datas as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }
        $this->optionHtml = sprintf($this->optionHtml,"v-for='item in radioData{$this->varMark}' :key='item.value' :label='item.value'");
        $this->setAttr('data', $options);
        return $this;
    }
    /**
     * 按钮样式
     */
    public function themeButton(){
        $this->optionHtml  = str_replace('el-radio','el-radio-button',$this->optionHtml);
        return $this;
    }
    /**
     * 带边框样式
     */
    public function themeBorder(){
        $this->optionHtml  = str_replace('<el-radio','<el-radio border',$this->optionHtml);
        return $this;
    }
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<el-radio-group {$attrStr}>{$this->optionHtml}</el-radio-group>";
        return $html;
    }

}
