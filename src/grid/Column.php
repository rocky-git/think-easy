<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-14
 * Time: 21:31
 */

namespace thinkEasy\grid;


use thinkEasy\View;

class Column extends View
{
    protected $attrs = [
        'index',
        'render-header',
        'sort-method',
        'sort-by',
        'sort-orders',
        'formatter',
        'selectable',
        'reserve-selection',
        'filters',
        'filter-method',
        'filtered-value',
    ];
    protected $scopeTemplaet = '<template slot-scope="scope">%s</template>';
    //自定义内容
    protected $display = '';

    //字段
    protected $field = '';

    //组件行字段
    protected $rowField = '';

    //内容颜射
    protected $usings = [];
    //映射标签颜色
    protected $tagColor = [];
    //映射标签颜色主题
    protected $tagTheme = 'light';
    //标签
    protected $tag = '';
    //自定义闭包
    protected $displayClosure = null;
    //
    protected $cellVue;
    public function __construct($field='', $label='')
    {
        if(!empty($field)){
            $this->field= $field;
            $this->rowField = "scope.row.{$this->field}";
            $this->setAttr('prop',$field);
        }
        if(!empty($label)){
            $this->setAttr('label',$label);
        }
    }

    /**
     * 设置当内容过长被隐藏时显示
     * @return $this
     */
    public function tip(){
        $this->setAttr('show-overflow-tooltip',true);
        return $this;
    }
    /**
     * 设置宽度
     * @param int $number
     */
    public function width(int $number){
        $this->setAttr('width',$number);
        return $this;
    }

    /**
     * 对齐方式
     * @param $align 左对齐 left/ 居中 center/ 右对齐 right
     */
    public function align($align){
        $this->setAttr('align',$align);
        return $this;
    }
    /**
     * 设置最小宽度
     * @param int $number
     */
    public function minWidth(int $number){
        $this->setAttr('min-width',$number);
        return $this;
    }
    /**
     * 标签显示
     * @param $color 标签颜色：success，info，warning，danger
     * @param $theme 主题：dark，light，plain
     */
    public function tag($color='',$theme='dark'){
        $this->tag = "<el-tag effect='{$theme}' type='{$color}'>%s</el-tag>";
        return $this;
    }

    /**
     * 内容映射
     * @param array $usings 映射内容
     * @param array $tagColor 标签颜色
     * @param tagTheme 标签颜色主题：dark，light，plain
     */
    public function using(array $usings,array $tagColor=[],$tagTheme='light'){
        $this->tagColor = $tagColor;
        $this->tagTheme = $tagTheme;
        $this->usings = $usings;
        return $this;
    }

    /**
     * 自定义显示
     * @param \Closure $closure
     * @return $this
     */
    public function display(\Closure $closure)
    {
        $this->displayClosure = $closure;
        return $this;
    }
    /**
     * 设置数据
     * @param $data
     */
    public function setData($data){
        if (!is_null($this->displayClosure)) {
            $res = call_user_func_array($this->displayClosure, [$data[$this->field], $data]);
            $this->cellVue.="<span v-if='data.id == {$data['id']}'>{$res}</span>";
        }
    }
    public function getDisplay($key,$tableDataScriptVar){
        if(!empty($this->cellVue)){
            $this->display = '<component :is="cellComponent['.$key.']" :data="scope.row" :index="scope.$index" :showEditId.sync="showEditId" :tableData="'.$tableDataScriptVar.'"></component>';
            $cell = new Cell();
            $cell->setVar('cell',$this->cellVue);
            $this->cellVue =$cell->render();
        }
        return $this->cellVue;

    }
    public function render(){
        if(empty($this->display)){
            if(!empty($this->tag)){
                $this->display = sprintf($this->tag,"{{{$this->rowField}}}");
            }
            if(count($this->usings) > 0){
                $html = '';
                foreach($this->usings as $key=>$value){
                    if(is_string($key)){
                        $html.="<span v-if=\"{$this->rowField} == '{$key}'\">%s</span>";
                    }else{
                        $html.="<span v-if='{$this->rowField} == {$key}'>%s</span>";
                    }
                    if(isset($this->tagColor[$key])){
                        $this->tag($this->tagColor[$key],$this->tagTheme);
                        $value = sprintf($this->tag,$value);
                    }
                    $html = sprintf($html,$value);
                }
                $this->display = sprintf($this->scopeTemplaet,$html);
            }
        }else{
            $this->display = sprintf($this->scopeTemplaet,$this->display);
        }
        list($attrStr,$dataStr) = $this->parseAttr();
        return "<el-table-column $attrStr>".$this->display."</el-table-column>";
    }
}
