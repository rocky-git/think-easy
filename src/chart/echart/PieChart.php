<?php

namespace thinkEasy\chart\echart;


use thinkEasy\chart\EchartAbstract;

/**
 * 饼图
 * Class PieChart
 * @package thinkEasy\chart
 */
class PieChart extends EchartAbstract
{
    public function __construct($height='350px',$width='100%')
    {
        parent::__construct($height,$width);
        $this->options = [
            'title' => [
                'text' => '',
            ],
            'tooltip' => [
                'trigger' => 'item',
                'formatter' => '{a} <br/>{b} : {c} ({d}%)'
            ],

            'legend' => [
                'left' => 'center',
                'bottom' => '0',
                'data' => [],
            ],
            'series' => []
        ];
    }

    /**
     * 设置数据源
     * @param string $name
     * @param array $data
     */
    public function series(string $name,array $data){
        $names = array_column($data,'name');
        $this->legend = array_merge($this->legend,$names);
        $length = count($this->series);
        $start = $length * 30+ 10;
        $end = ($length+1) * 20;
        $this->series[] = [
            'name'=>$name,
            'type'=>'pie',
            'roseType'=>'radius',
            'radius'=> [$start, $start+$end],
            'center'=> ['50%', '38%'],
            'animationEasing'=>'cubicInOut',
            'animationDuration'=>2600,
            'symbolSize'=>8,
            'data'=>$data,
        ];
        return $this;
    }

}
