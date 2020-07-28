<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-06-23
 * Time: 20:07
 */

namespace thinkEasy\chart;


use think\db\Query;
use think\facade\Db;
use think\facade\Request;
use think\Model;
use thinkEasy\chart\echart\FunnelChart;
use thinkEasy\chart\echart\LineChart;
use thinkEasy\chart\echart\PieChart;
use thinkEasy\chart\echart\RadarChart;
use thinkEasy\grid\Filter;
use thinkEasy\tools\DateTime;
use thinkEasy\View;

/**
 * Class Echarts
 * @package buildView
 * @method $this count($text, \Closure $query) 统计数量
 * @method $this max($text, $filed, \Closure $query) 统计最大值
 * @method $this avg($text, $field, \Closure $query) 统计平均值
 * @method $this sum($text, $field, \Closure $query) 统计总和
 * @method $this min($text, $field, \Closure $query) 统计最小值
 */
class Echart extends View
{
    protected $db;
    protected $chart;
    protected $dateField;
    protected $filter = null;
    protected $chartType = 'line';
    protected $seriesData = [];
    protected $title = '';
    protected $radarData = [];
    protected $groupSeries = [];
    protected $radarMaxKey = -1;
    /**
     * Echart constructor.
     * @param $title 标题
     * @param string $type 图表类型 line折线，bar柱状 pie饼图 radar雷达图 funnel
     * @param string $height 图表高度
     */
    public function __construct($title, $type = 'line', $height = "350px")
    {
        $this->title = $title;
        $this->setVar('title', $this->title);
        $this->setVar('height', $height);
        $this->chartType = $type;
        if ($this->chartType == 'line' || $this->chartType == 'bar') {
            $this->chart = new LineChart($height, "100%", $this->chartType);
        } elseif ($this->chartType == 'pie') {
            $this->chart = new PieChart($height, '100%');
        } elseif ($this->chartType == 'funnel') {
            $this->chart = new FunnelChart($height, '100%');
        } elseif ($this->chartType == 'radar') {
            $this->chart = new RadarChart($height, '100%');
        }
    }

    /**
     * 当前图表
     * @return RadarChart
     */
    public function chart()
    {
        return $this->chart;
    }

    /**
     * 设置表名数据源
     * @param $table 模型或表名
     * @param string $dateField 日期字段
     */
    public function table($table, $dateField = 'create_time')
    {
        $this->template = 'echart';
        if ($table instanceof Model) {
            $this->db = $table;
        } elseif ($table instanceof Query) {
            $this->db = $table;
        } else {
            $this->db = Db::name($table);
        }
        $this->dateField = $dateField;
    }

    /**
     * 查询过滤
     * @param $callback
     */
    public function filter($callback)
    {
        if ($callback instanceof \Closure) {
            $this->filter = new Filter($this->db);
            call_user_func($callback, $this->filter);
            $this->setVar('filter', $this->filter->render());
        }

    }

    public function __call($name, $arguments)
    {
        if ($name == 'count') {
            $text = array_shift($arguments);
            if ($this->chartType == 'line' || $this->chartType == 'bar') {
                $this->lineAnalyze($name, $this->db->getPk(), $text, end($arguments));
            } elseif ($this->chartType == 'pie' || $this->chartType == 'funnel') {
                $this->pieAnalyze($name, $this->db->getPk(), $text, end($arguments));
            } elseif ($this->chartType == 'radar') {
                $max = array_shift($arguments);
                if ($max instanceof \Closure) {
                    $max = 100;
                }
                $this->radarAnalyze($name, $this->db->getPk(), $text, $max, end($arguments));
            }

        } else {
            list($text, $field) = $arguments;
            if ($this->chartType == 'line' || $this->chartType == 'bar') {
                $this->lineAnalyze($name, $field, $text, end($arguments));
            } elseif ($this->chartType == 'pie' || $this->chartType == 'funnel') {
                $this->pieAnalyze($name, $field, $text, end($arguments));
            } elseif ($this->chartType == 'radar') {
                if (isset($arguments[2])) {
                    $max = $arguments[2];
                } else {
                    $max = 100;
                }
                $this->radarAnalyze($name, $field, $text, $max, end($arguments));
            }
        }
        return $this;
    }

    /**
     * 分组
     * @param $name 组名
     * @param \Closure $closure
     */
    public function group($name, \Closure $closure)
    {
        call_user_func($closure, $this);
        if ($this->chart instanceof RadarChart) {
            $this->groupSeries[] = [
                'name' => $name,
                'value' => $this->seriesData,
            ];
        } else {
            $this->chart->series($name, $this->seriesData);
        }
        $this->seriesData = [];
    }

    protected function radarAnalyze($type, $field, $name, $max = 100, $closure = null)
    {
        $date_type = Request::get('date_type', 'today');
        $db = clone $this->db;
        if ($closure instanceof \Closure) {
            call_user_func($closure, $db);
        }
        switch ($date_type) {
            case 'yesterday':
            case 'today':
                $value = $db->whereDay($this->dateField, $date_type)->$type($field);
                break;
            case 'week':
                $value = $db->whereWeek($this->dateField)->$type($field);
                break;
            case 'month':
                $months = DateTime::thisMonths();
                $value = $db->whereMonth($this->dateField)->$type($field);
                break;
            case 'year':
                $value = $db->whereYear($this->dateField)->$type($field);
                break;
            case 'range':
                $start_date = Request::get('start_date');
                $end_date = Request::get('end_date');
                $dates = DateTime::rangeDates($start_date, $end_date);
                $value = $db->whereBetweenTime($this->dateField, $start_date, $end_date)->$type($field);
                break;
        }
        $this->chart->indicator($name, $max);
        $key = $this->chart()->getIndicatorKey($name);
        if($this->radarMaxKey < $key){
            $this->radarMaxKey = $key;
        }
        $this->seriesData[$key] = $value;
    }

    protected function pieAnalyze($type, $field, $name, $closure = null)
    {
        $date_type = Request::get('date_type', 'today');
        $db = clone $this->db;
        if ($closure instanceof \Closure) {
            call_user_func($closure, $db);
        }
        switch ($date_type) {
            case 'yesterday':
            case 'today':
                $value = $db->whereDay($this->dateField, $date_type)->$type($field);
                break;
            case 'week':
                $value = $db->whereWeek($this->dateField)->$type($field);
                break;
            case 'month':
                $months = DateTime::thisMonths();
                $value = $db->whereMonth($this->dateField)->$type($field);
                break;
            case 'year':
                $value = $db->whereYear($this->dateField)->$type($field);
                break;
            case 'range':
                $start_date = Request::get('start_date');
                $end_date = Request::get('end_date');
                $dates = DateTime::rangeDates($start_date, $end_date);
                $value = $db->whereBetweenTime($this->dateField, $start_date, $end_date)->$type($field);
                break;
        }
        $this->seriesData[] = [
            'name' => $name,
            'value' => $value
        ];
    }


    protected function lineAnalyze($type, $field, $name, $closure = null)
    {
        $date_type = Request::get('date_type', 'today');
        $series = [];
        $xAxis = [];
        switch ($date_type) {
            case 'yesterday':
            case 'today':
                if ($date_type == 'yesterday') {
                    $date = date('Y-m-d', strtotime(' -1 day'));
                } else {
                    $date = date('Y-m-d');
                }
                for ($i = 0; $i < 24; $i++) {
                    $j = $i + 1;
                    $hour = $i < 10 ? '0' . $i : $i;
                    $xAxis[] = "{$i}点到{$j}点";
                    $db = clone $this->db;
                    if ($closure instanceof \Closure) {
                        call_user_func($closure, $db);
                    }
                    $series[] = $db->whereBetween($this->dateField, ["{$date} {$hour}:00:00", "{$date} {$hour}:59:59"])->$type($field);

                }
                break;
            case 'week':
                $start_week = date('Y-m-d', strtotime('this week'));;
                for ($i = 0; $i <= 6; $i++) {
                    $week = DateTime::afterDate($i, $start_week);
                    $xAxis[] = $week;
                    $db = clone $this->db;
                    if ($closure instanceof \Closure) {
                        call_user_func($closure, $db);
                    }
                    $series[] = $db->whereDay($this->dateField, $week)->$type($field);
                }
                break;
            case 'month':
                $months = DateTime::thisMonths();
                foreach ($months as $month) {
                    $xAxis[] = $month;
                    $db = clone $this->db;
                    if ($closure instanceof \Closure) {
                        call_user_func($closure, $db);
                    }
                    $series[] = $db->whereDay($this->dateField, $month)->$type($field);
                }
                break;
            case 'year':
                for ($i = 1; $i <= 12; $i++) {
                    $xAxis[] = $i . '月';
                    $db = clone $this->db;
                    if ($closure instanceof \Closure) {
                        call_user_func($closure, $db);
                    }
                    $series[] = $db->whereMonth($this->dateField, date("Y-{$i}"))->$type($field);
                }
                break;
            case 'range':
                $start_date = Request::get('start_date');
                $end_date = Request::get('end_date');
                $dates = DateTime::rangeDates($start_date, $end_date);
                foreach ($dates as $date) {
                    $xAxis[] = $date;
                    $db = clone $this->db;
                    if ($closure instanceof \Closure) {
                        call_user_func($closure, $db);
                    }
                    $series[] = $db->whereDay($this->dateField, $date)->$type($field);
                }
                break;
        }
        $this->chart->xAxis($xAxis)->series($name, $series);
    }

    /**
     * 返回视图
     * @return string
     */
    public function view()
    {
        if ($this->chart instanceof RadarChart) {
            $seriesData[] = [
                'name' => $this->title,
                'value' => $this->seriesData
            ];
            $this->seriesData = $seriesData;
            if (count($this->groupSeries) > 0) {
                $groupSeries  = $this->groupSeries;
                $series = [];
                foreach ($groupSeries as $key=>$groupSerie){
                    $series[$key]['name'] = $groupSerie['name'];
                    for ($i=0;$i<=$this->radarMaxKey;$i++){
                        if(isset($groupSerie['value'][$i])){
                            $series[$key]['value'][$i] = $groupSerie['value'][$i];
                        }else{
                            $series[$key]['value'][$i]  = 0;
                        }
                    }
                }
                $this->seriesData = $series;
            }
        }
        if (count($this->seriesData) > 0) {
            $this->chart->series($this->title, $this->seriesData);
        }
        $html = $this->chart->render();
        $html = "<template>{$html}</template>";
        $moudel = app('http')->getName();
        $node = $moudel . '/' . request()->pathinfo();
        $this->setVar('url', $node);
        if (Request::has('ajax')) {
            return $html;
        }
        $this->setVar('html', rawurlencode($html));
        return parent::render();
    }
}
