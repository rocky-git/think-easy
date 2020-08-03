<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-21
 * Time: 19:19
 */

namespace thinkEasy\form;

class Select extends Field
{
    protected $optionHtml;
    protected $attrs = [
        'data',
        'clearable',
        'filterable',
        'disabled',
        'multiple',
        'readonly',
    ];
    protected $options = [];
    protected $groupOptions = [];
    protected $disabledData = [];
    public function __construct($field, $label, $arguments = [])
    {
        parent::__construct($field, $label, $arguments);
        $this->setAttr('clearable', true);
        $this->setAttr('filterable', true);
        $this->setAttr('placeholder', '请选择' . $label);
    }

    /**
     * 设置宽度
     * @param string $num
     */
    public function width($num = '100%')
    {
        $this->setAttr('style', 'width:' . $num);
        return $this;
    }
    /**
     * 设置分组选项数据
     * @param array $datas
     * @return $this
     */
    public function groupOptions(array $datas)
    {
        /* 格式
         $datas = [
            [
                'label' => '第一个分组',
                'value' => 2,
                'options' => [
                    [
                        'label' => '第一个标签',
                        'value' => 1
                    ]
                ]
            ],
            [
                'label' => '第二个分组',
                'value' => 2,
                'options' => [
                    [
                        'label' => '第二个标签',
                        'value' => 2
                    ]
                ]
            ]
         ];
        */
        $this->groupOptions = $datas;
        $this->optionHtml = "<el-option-group
      v-for='group in selectData{$this->varMark}'
      :key='group.value'
      :label='group.label'
      :disabled='group.disabled'>
      <el-option
      v-for='item in group.options'
      :key='item.value'
      :label='item.label'
      :value='item.value'
      :disabled='item.disabled'>
      <span v-html='item.label'></span>
    </el-option>
    </el-option-group>";
        return $this;

    }

    /**
     * 设置选项数据
     * @param array $datas
     */
    public function options(array $datas)
    {
        $this->options = $datas;
        $this->optionHtml = "<el-option
      v-for='item in selectData{$this->varMark}'
      :key='item.value'
      :label='item.label'
      :value='item.value'
      :disabled='item.disabled'>
      <span v-html='item.label'></span>
    </el-option>";
        return $this;
    }
    /**
     * 多选
     */
    public function multiple()
    {
        $this->setAttr('multiple', true);
        return $this;
    }
    /**
     * 禁用选项数据
     * @param array $data 禁用数据
     */
    public function disabledData(array $data){
        $this->disabledData = $data;
    }
    protected function parseOptions(){
        $options = [];
        foreach ($this->options as $value=>$label){
            if(in_array($value,$this->disabledData)){
                $disabled = true;
            }else{
                $disabled = false;
            }
            $options[] = [
                'value' => $value,
                'label' => $label,
                'disabled' => $disabled,
            ];
        }
        $this->setAttr('data', $options);
        $groupOptions = [];
        foreach ($this->groupOptions as $key=>$option){
            if(in_array($option['value'],$this->disabledData)){
                $disabled = true;
            }else{
                $disabled = false;
            }
            $group = [
                'value' => $option['value'],
                'label' => $option['label'],
                'disabled' => $disabled,
            ];
            foreach ($option['options'] as $item){
                if(in_array($item['value'],$this->disabledData)){
                    $disabled = true;
                }else{
                    $disabled = false;
                }
                $group['options'][] = [
                    'value' => $item['value'],
                    'label' => $item['label'],
                    'disabled' => $disabled,
                ];
            }
            $groupOptions[] = $group;
        }
        if(count($groupOptions) > 0){
            $this->setAttr('data', $groupOptions);
        }
    }
    public function inOptions($val){
        $keys = array_keys($this->options);
        if(in_array($val,$keys)){
            return true;
        }else{
            return false;
        }
    }
    public function render()
    {
        $this->parseOptions();
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<el-select {$attrStr}>
    {$this->optionHtml}
  </el-select>";
        return $html;
    }
}
