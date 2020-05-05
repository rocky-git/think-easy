<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-29
 * Time: 21:52
 */

namespace thinkEasy\form;


use thinkEasy\View;

class Button extends View
{
    /**
     * Button constructor.
     * @param $text 按钮文字
     * @param string $type 颜色类型 primary / success / warning / danger / info / text
     * @param string $size 尺寸 medium / small / mini
     * @param string $icon 图标
     */
    public function __construct($text='',$type='',$size='small',$icon='',$plain=false)
    {
        $this->template  = 'button';
        $this->text  = $text;
        $this->setAttr('type',$type);
        $this->setAttr('size',$size);
        $this->setAttr('icon',$icon);
        $this->setAttr('text',$text);
        if($plain){
            $this->setAttr('plain','true');
        }


    }
    //禁用状态
    public function disabled(){
        $this->setAttr('disabled','true');
        return $this;
    }
    //圆形按钮
    public function circle(){
        $this->setAttr('circle','true');
        return $this;
    }
    //圆角按钮
    public function round(){
        $this->setAttr('round','true');
        return $this;
    }
    /**
     * 打开窗口 modal弹窗对话框 open当前窗口
     * @Author: rocky
     * 2019/9/11 10:02
     * @param $url 跳转链接
     * @param string $type 跳转类型
     */
    public function href($url,$type='open'){
        if(empty($url)){
            $url = request()->url();
        }
        $this->setAttr('url',$url);
        $this->setAttr('open-type',$type);
    }
//    public function hrefEdit($id,$controller,$module='admin',$type='open'){
//        $url = "/$module/$controller/$id/edit.rest";
//        $this->href($url);
//    }
    public function render(){
        list($attrStr, $scriptVar) = $this->parseAttr();
        $html = "<eadmin-button {$attrStr}></eadmin-button>";
        return $html;
    }
}