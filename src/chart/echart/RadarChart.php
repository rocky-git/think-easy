<?php

namespace thinkEasy\chart\echart;

use thinkEasy\chart\EchartAbstract;


/**
 * 雷达图
 * Class RadarChart
 * @package thinkEasy\chart
 */
class RadarChart extends EchartAbstract
{
    protected $indicator = [];
    public function __construct($height = '350px', $width = '100%')
    {
        parent::__construct($height, $width);
        $this->options = [
            'title' => [
                'text' => '',
            ],
            'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => [
                    'type' => 'shadow'
                ]
            ],
            'radar' => [
                'radius' => '66%',
                'center' => ['50%', '42%'],
                'splitNumber' => 5,
                'splitArea' => [
                    'areaStyle' => [
                        'color' => 'rgba(127,95,132,.3)',
                        'opacity' => 1,
                        'shadowBlur' => 45,
                        'shadowColor' => 'rgba(0,0,0,.5)',
                        'shadowOffsetX' => 0,
                        'shadowOffsetY' => 15
                    ]
                ],
                'indicator' => [],
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
    public function series(string $name, array $data)
    {
        $names = array_column($data, 'name');
        $this->legend = array_merge($this->legend, $names);

        $this->series[] = [
            'name' => $name,
            'type' => 'radar',
            'tooltip' => [
                'trigger' => 'item'
            ],
            'data' => $data,
        ];
        return $this;
    }

    /**
     * 添加标志点
     * @param $name 名称
     * @param $max 最大值
     */
    public function indicator($name, $max)
    {
        $key = $this->getIndicatorKey($name);
        $count = count($this->indicator);
        if ($key == $count) {
            $this->indicator[] = [
                'name' => $name,
                'max' => $max
            ];
        }
    }

    public function getIndicatorKey($name)
    {
        $names = array_column($this->indicator, 'name');
        $res = array_search($name, $names);
        if ($res === false) {
            return count($this->indicator);
        } else {
            return $res;
        }
    }

    /**
     * 返回视图
     * @return string
     */
    public function render()
    {
        if(!empty($this->legend)){
            $this->options['radar']['indicator'] = $this->indicator;
        }
        return parent::render();
    }
}
