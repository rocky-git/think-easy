<?php


namespace thinkEasy\layout;


use thinkEasy\View;

class Column extends View
{
    protected $html = '';
    protected $span = 24;
    protected $component = [];
    /**
     * 添加一行
     * @param $content
     */
    public function row($content){
        $row = new Row();
        if($content instanceof \Closure){
            call_user_func($content,$row);
        }else{
            $row->column($content);
        }
        $this->html .= $row->render();
        $this->component = array_merge($this->component,$row->getComponents());
    }
    /**
     * 添加一行组件
     * @param $component 组件
     * @param $span 栅格占据的列数,默认24
     */
    public function rowComponent($component,$span = 24){
        $row = new Row();
        $row->columnComponent($component,$span);
        $this->html .= $row->render();
        $this->component = array_merge($this->component,$row->getComponents());
    }
    /**
     * 添加一行组件
     * @param $url 组件url
     * @param $span 栅格占据的列数,默认24
     */
    public function rowComponentUrl($url,$span = 24){
        $row = new Row();
        $row->columnComponentUrl($url,$span);
        $this->html .= $row->render();
        $this->component = array_merge($this->component,$row->getComponents());
    }
    public function getComponents(){
        return $this->component;
    }

    /**
     * 添加内容
     * @param $html
     */
    public function content($html){
        $this->html = $html;
    }
    /**
     * 栅格占据的列数
     * @param $num 默认24
     */
    public function span($num = 24){
        $this->setAttr(':span',$num);
    }
    /**
     * 栅格左侧的间隔格数
     * @param $num
     */
    public function offset($num){
        $this->setAttr(':offset',$num);
    }
    /**
     * 栅格向右移动格数
     * @param $num 默认24
     */
    public function push($num){
        $this->setAttr(':push',$num);
    }
    /**
     * 栅格向左移动格数
     * @param $num
     */
    public function pull($num){
        $this->setAttr(':pull',$num);
    }
    public function render(){
        list($attrStr, $scriptVar) = $this->parseAttr();
        return "<el-col $attrStr>{$this->html}</el-col>";
    }
}
