<?php

namespace thinkEasy\layout;
use thinkEasy\View;

class Content extends View
{
    protected $html = '';
    public function __construct()
    {
        $this->template = 'content';
    }

    /**
     * 添加一行
     * @param $content 内容
     * @param $span 栅格占据的列数,默认24
     */
    public function row($content,$span = 24){
        $row = new Row();
        if($content instanceof \Closure){
            call_user_func($content,$row);
        }else{
            $row->column($content,$span);
        }
        $this->html .= $row->render();
    }
    public function view(){
        $this->setVar('html',$this->html);
        return $this->render();
    }
}
