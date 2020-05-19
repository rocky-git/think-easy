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
    protected $optionHtml;
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
        $this->optionHtml = "<el-checkbox-button
      v-for='item in checkboxData{$this->varMatk}'
      :key='item.value'
      :label='item.value'>
      {{item.label}}
    </el-checkbox-button>";
        $this->setAttr('data', $options);
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