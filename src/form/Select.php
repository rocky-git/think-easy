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
    public function __construct($field, $label)
    {
        parent::__construct($field, $label);
        $this->setAttr('clearable',true);
        $this->setAttr('filterable',true);
        $this->setAttr('placeholder', '请选择' . $label);
    }

    /**
     * 设置宽度
     * @param string $num
     */
    public function width($num = '100%'){
        $this->setAttr('style','width:'.$num);
        return $this;
    }
    /**
     * 设置分组选项数据
     * @param array $datas
     * @return $this
     */
    public function groupOptions(array $datas){
        /* 格式

         $datas = [
            [
                'label' => '第一个分组',
                'options' => [
                    [
                        'label' => '第一个标签',
                        'value' => 1
                    ]
                ]
            ],
            [
                'label' => '第二个分组',
                'options' => [
                    [
                        'label' => '第二个标签',
                        'value' => 2
                    ]
                ]
            ]
         ]；

        */
        $this->optionHtml = "<el-option-group
      v-for='group in selectData{$this->varMatk}'
      :key='group.label'
      :label='group.label'>
      <el-option
      v-for='item in group.options'
      :key='item.value'
      :label='item.label'
      :value='item.value'>
      <span v-html='item.label'></span>
    </el-option>
    </el-option-group>";
        $this->setAttr('data',$datas);
        return $this;

    }
    /**
     * 设置选项数据
     * @param array $datas
     */
    public function options(array $datas){
        $options = [];
        foreach ($datas as $value=>$label){
            $options[] = [
              'value'=>$value,
              'label'=>$label,
            ];
        }
        $this->optionHtml = "<el-option
      v-for='item in selectData{$this->varMatk}'
      :key='item.value'
      :label='item.label'
      :value='item.value'>
      <span v-html='item.label'></span>
    </el-option>";
        $this->setAttr('data',$options);
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

    public function render(){
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<el-select {$attrStr}>
    {$this->optionHtml}
  </el-select>";
        return $html;
    }
}
