<?php


namespace thinkEasy\layout;


use thinkEasy\View;

class Row extends View
{
    protected $html = '';
    protected $gutter = 0;
    /**
     * 添加列
     * @param $content 内容
     * @param $span 栅格占据的列数,占满一行24,默认24
     */
    public function column($content,$span = 24){
        $column = new Column();
        $column->span($span);

        if($content instanceof \Closure){
            call_user_func($content,$column);
        }else{
            $column->content($content);
        }

        $this->html .= $column->render();
    }

    /**
     * flex布局
     * @param $justify flex 布局下的水平排列方式
     * @param $align flex 布局下的垂直排列方式
     */
    public function flex($justify,$align){
        $this->setAttr(':type','flex');
        $this->setAttr(':justify',$justify);
        $this->setAttr(':align',$align);
    }
    /**
     * 设置栅格间隔
     * @param $number
     */
    public function gutter($number){
        $this->setAttr(':gutter',$number);
    }
    public function render(){
        list($attrStr, $scriptVar) = $this->parseAttr();
        return "<el-row style=\"margin-bottom: 15px;\" $attrStr>{$this->html}</el-row>";
    }
}
