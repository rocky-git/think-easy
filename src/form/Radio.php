<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-19
 * Time: 21:50
 */

namespace thinkEasy\form;


class Radio extends Field
{
    protected $attrs = [
        'data',
        'disabled',
    ];
    protected $optionHtml;
    protected $eventJs = null;
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
        $this->optionHtml = "<el-radio
      v-for='item in radioData{$this->varMatk}'
      :key='item.value'
      :label='item.value'>
      {{item.label}}
    </el-radio>";
        $this->setAttr('data', $options);
        return $this;
    }
    public function render()
    {
        $this->setAttr('@change',"(e)=>radioChange(e,\"{$this->getTag()}\",manyIndex)");
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<el-radio-group {$attrStr}>{$this->optionHtml}</el-radio-group>";
        return $html;
    }

}
