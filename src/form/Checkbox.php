<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-19
 * Time: 21:50
 */

namespace thinkEasy\form;


class Checkbox extends Field
{
    protected $attrs = [
        'data',
        'disabled',
    ];
    protected $optionHtml = '<el-checkbox %s>{{item.label}}</el-checkbox>';
    public function __construct($field, $label, array $arguments = [])
    {
        parent::__construct($field, $label, $arguments);
    }
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
        $this->optionHtml = sprintf($this->optionHtml,"v-for='item in checkboxData{$this->varMark}' :key='item.value' :label='item.value'");
        $this->setAttr('data', $options);
        return $this;

    }

    /**
     * 按钮样式
     */
    public function themeButton(){
        $this->optionHtml  = str_replace('el-checkbox','el-checkbox-button',$this->optionHtml);
        return $this;
    }
    /**
     * 带边框样式
     */
    public function themeBorder(){
        $this->optionHtml  = str_replace('<el-checkbox','<el-checkbox border',$this->optionHtml);
        return $this;
    }
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<el-checkbox-group {$attrStr}>
    {$this->optionHtml}
  </el-checkbox-group>";
        return $html;
    }
}
