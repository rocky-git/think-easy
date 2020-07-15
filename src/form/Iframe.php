<?php


namespace thinkEasy\form;


class Iframe extends Field
{
    protected $attrs = [
        'data',
    ];
    protected $options = [];
    protected $optionHtml;
    protected $multiple = 0;
    protected $url = '';
    protected $text = '';
    /**
     * 多选
     */
    public function multiple()
    {
        $this->multiple = 1;
        return $this;
    }
    /**
     * 设置数据源
     * @param $datas 数据
     * @param $url url
     * @param $text 按钮文本
     * @return $this
     */
    public function data(array $datas,$url,$text='请选择'){
        $this->iframeUrl = $url;
        $this->options = $datas;
        $this->url = $url;
        $this->text = $text;
        return $this;
    }
    public function render()
    {
        $this->optionHtml = "<el-tag
  v-for='(item,iframeIndex) in iframeData{$this->varMark}'
  :key='item.id'
  closable
  style='margin-right: 10px;'
  v-show='item.show'
  @close='handleTagClose(iframeData{$this->varMark},iframeIndex,item.id,\"{$this->field}\")'>
  {{item.label}}
</el-tag>";
        if($this->multiple){
            $this->optionHtml.="<div><el-button size='small' @click='iframeClick(iframeData{$this->varMark},\"{$this->url}\",\"{$this->field}\",{$this->multiple})'>{$this->text}</el-button><el-button size='small' v-show='this.form.{$this->field}.length > 0' type=\"danger\" @click='iframeClear(iframeData{$this->varMark},\"{$this->field}\")'>清空</el-button></div>";
        }else{
            $this->optionHtml.="<div><el-button size='small' @click='iframeClick(iframeData{$this->varMark},\"{$this->url}\",\"{$this->field}\",{$this->multiple})'>{$this->text}</el-button></div>";
        }
        $options = [];
        foreach ($this->options as $value=>$label){
            $options[] = [
                'id' => $value,
                'label' => $label,
                'show' => false,
            ];
        }
        $this->setAttr('data', $options);
        $this->script = <<<EOT
        this.iframeData{$this->varMark}.forEach(item=>{
            if(this.form.{$this->field}){
              
               if(this.form.{$this->field} instanceof Array && this.form.{$this->field}.indexOf(item.id.toString()) !== -1){
                
                    item.show = true
               }else if(this.form.{$this->field} == item.id){
                    item.show = true
               }
            }
        })
EOT;
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        return $this->optionHtml;
    }
}
