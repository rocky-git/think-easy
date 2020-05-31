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

    /**
     * 改变事件
     * @param $val  值
     * @param array $showTags 要显示的form标记元素
     * @param array $hideTags 要隐藏的form标记元素
     * @return $this
     */
    public function event($val,array $showTags,array $hideTags){
        if(!empty($showTags)){
            $this->showItem($val,$showTags); 
        }
        if(!empty($showTags)){
            $this->hideItem($val,$hideTags);
        }
        $this->script = "this.radioChange(this.form.{$this->field},'{$this->getTag()}',0)".PHP_EOL;
        return $this;
    }
    /**
     * 隐藏元素
     * @param $val 值
     * @param array $tags 要显示的form标记元素
     * @return $this
     */
    protected function hideItem($val,array $tags){
        $tags = array_map(function ($v){
            return "'{$v}' + manyIndex";
        },$tags);
        $tags = implode(',',$tags);
        $this->eventJs .= "if(val == '{$val}' && tag === '{$this->getTag()}'){this.formItemTags.splice(-1,0,{$tags})}".PHP_EOL;
        return $this;
    }
    /**
     * 显示元素
     * @param $val 值
     * @param array $tags 要隐藏的form标记元素
     * @return $this
     */
    protected function showItem($val,array $tags){
        foreach ($tags as $tag){
            $this->eventJs .= "if(val == '{$val}' && tag === '{$this->getTag()}'){this.deleteArr(this.formItemTags,'{$tag}' + manyIndex)}".PHP_EOL;
        }
        return $this;
    }
    public function getEventJs(){
        return $this->eventJs;
    }
    public function render()
    {
        $this->setAttr('@change',"(e)=>radioChange(e,\"{$this->getTag()}\",manyIndex)");
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<el-radio-group {$attrStr}>{$this->optionHtml}</el-radio-group>";
        return $html;
    }

}
