<?php

namespace thinkEasy\chart;
use thinkEasy\View;

/**
 * 折线图表
 * Class LineChart
 * @package thinkEasy\chart
 */
class LineChart extends View
{
    protected $series = [];
    protected $legend = [];
    public function __construct($height='350px',$width='100%')
    {
        $this->setAttr('width',$width);
        $this->setAttr('height',$height);
    }

    /**
     * 设置标题
     * @param $text
     */
    public function title($text){
        $this->setAttr('title',$text);
        return $this;
    }
    /**
     * 设置数据源
     * @param string $name
     * @param array $data
     */
    public function series(string $name,array $data){
        $this->legend[] = $name;
        $this->series[] = [
            'name'=>$name,
            'type'=>'line',
            'symbolSize'=>8,
            'data'=>$data,
        ];
        return $this;
    }
    /**
     * 设置X轴数据
     * @param array $data
     */
    public function xAxis(array $data){
        $this->setAttr(':x-axis',json_encode($data));
        return $this;
    }
    /**
     * 返回视图
     * @return string
     */
    public function render()
    {
        $this->setAttr(':series',json_encode($this->series));
        $this->setAttr(':legend',json_encode($this->legend));
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<eadmin-line-chart {$attrStr} />";
        return $html;
    }
}
