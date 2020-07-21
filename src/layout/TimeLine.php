<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-06-22
 * Time: 21:15
 */

namespace thinkEasy\layout;


use thinkEasy\View;

/**
 * 时间线
 * Class Timeline
 * @package thinkEasy\layout
 */
class TimeLine extends View
{
    protected $html = '';

    /**
     * 创建
     * @param array $datas
     * @return TimeLine
     */
    public function create(array $datas){
        $self = new self();
        $self->data($datas);
        return $self;
    }
    /**
     * 设置数据源
     * @param array $datas
     */
    public function data(array $datas){
        foreach ($datas as $value){
            $this->html .= "<el-timeline-item timestamp='{$value['time']}'>{$value['content']}</el-timeline-item>";
        }
    }

    /**
     * 小到大排序
     */
    public function asc(){
        $this->setAttr(':reverse','true');
        return $this;
    }
    public function render(){
        list($attrStr, $scriptVar) = $this->parseAttr();
        return "<el-timeline $attrStr>{$this->html}</el-timeline>";
    }
}
