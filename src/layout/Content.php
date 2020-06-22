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
    }
    public function view(){
        $this->setVar('html',$this->html);
        return $this->render();
    }
}
