<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-21
 * Time: 22:26
 */

namespace thinkEasy\form;


use thinkEasy\View;

class Drawer extends View
{
    protected $attrs = [
        'title',
        'modal-append-to-body',
        'close-on-click-modal',
        'fullscreen',
    ];
    public function __construct($title,$content)
    {
        $this->setAttr('title',$title);
        $this->content = $content;
    }
    public function getVisibleVar(){
        return 'drawerVisible'.$this->varMatk;
    }
    public function getTitleVar(){
        return 'drawerTitle'.$this->varMatk;
    }
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html ="<el-drawer :visible.sync='dialogVisible' $attrStr>{$this->content}</el-drawer>";
        return $html;

    }
}
