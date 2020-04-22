<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-21
 * Time: 22:26
 */

namespace thinkEasy\form;


use thinkEasy\View;

class Dialog extends View
{
    protected $attrs = [
        'title',
    ];
    public function __construct($title,$content)
    {
        $this->setAttr('title',$title);
        $this->content = $content;
    }
    public function getVisibleVar(){
        return 'dialogVisible'.$this->varMatk;
    }
    public function getTitleVar(){
        return 'dialogTitle'.$this->varMatk;
    }
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html ="<el-dialog :visible.sync='dialogVisible' $attrStr>{$this->content}</el-dialog>";
        return $html;

    }
}
