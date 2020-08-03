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
    protected $placement = '';
    protected $datas = [];
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
        $this->datas = $datas;
    }

    /**
     * 时间戳置于内容之上
     * @return $this
     */
    public function timeTop(){
        $this->placement = "placement='top'";
        return $this;
    }
    /**
     * 小到大排序
     */
    public function asc(){
        $this->setAttr(':reverse','true');
        return $this;
    }
    public function render(){
        foreach ($this->datas as $value){
            $this->html .= "<el-timeline-item {$this->placement} timestamp='{$value['time']}'>{$value['content']}</el-timeline-item>";
        }
        list($attrStr, $scriptVar) = $this->parseAttr();
        return "<el-timeline $attrStr>{$this->html}</el-timeline>";
    }
}
