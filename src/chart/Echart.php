<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-06-23
 * Time: 20:07
 */

namespace thinkEasy\chart;


use think\facade\Db;
use think\facade\Request;
use thinkEasy\tools\DateTime;
use thinkEasy\View;

/**
 * Class Echarts
 * @package buildView
 * @method $this count($text) 统计数量
 * @method $this max($text, $filed) 统计最大值
 * @method $this avg($text, $field) 统计平均值
 * @method $this sum($text, $field) 统计总和
 * @method $this min($text, $field) 统计最小值
 */
class Echart extends View
{
    protected $db;
    protected $chart;
    protected $dateField;

    public function __construct()
    {
        $this->template = 'echart';
    }

    public function create($table, $title = '', $filter = '', $type = 'line', $dateField = 'create_time')
    {
        if ($table instanceof Model) {
            $this->db = $table;
        } elseif ($table instanceof Query) {
            $this->db = $table;
        } else {
            $this->db = Db::name($table);
        }
        if ($type == 'line') {
            $this->chart = new LineChart();
        }
        if (!empty($title)) {
            $this->chart->title($title);
        }
        $this->dateField = $dateField;
    }

    public function __call($name, $arguments)
    {
        if ($name == 'count') {
            $text = array_shift($arguments);
            $this->analyze($name, $this->db->getPk(), $text);
        } else {
            list($text, $field) = $arguments;
            $this->analyze($name, $field, $text);
        }
        return $this;
    }

    protected function analyze($type, $field, $name)
    {
        $date_type = Request::get('date_type','today');
        $series = [];
        $xAxis = [];
        $table = $this->db->getTable();
        switch ($date_type){
            case 'yesterday':
            case 'today':
                if($date_type == 'yesterday'){
                    $date = date('Y-m-d', strtotime(' -1 day'));
                }else{
                    $date = date('Y-m-d');
                }
                for ($i = 0; $i < 24; $i++) {
                    $j = $i + 1;
                    $hour = $i < 10 ? '0' . $i : $i;
                    $xAxis[] = "{$i}点到{$j}点";
                    $series[] = Db::name($table)->whereBetween($this->dateField, ["{$date} {$hour}:00:00", "{$date} {$hour}:59:59"])->$type($field);

                }
                break;
            case 'week':
                $start_week = date('Y-m-d',strtotime('this week'));;
                for ($i = 0; $i <= 6; $i++) {
                    $week = DateTime::afterDate($i,$start_week);
                    $xAxis[] = $week;
                    $series[] = Db::name($table)->whereDay($this->dateField, $week)->$type($field);
                }
                break;
            case 'month':
                $months = DateTime::thisMonths();
                foreach ($months as $month){
                    $xAxis[] = $month;
                    $series[] = Db::name($table)->whereDay($this->dateField, $month)->$type($field);
                }
                break;
            case 'year':
                for ($i = 1; $i <= 12; $i++) {
                    $xAxis[] = $i.'月';
                    $series[] = Db::name($table)->whereMonth($this->dateField, date("Y-{$i}"))->$type($field);
                }
                break;
            case 'range':
                $start_date = Request::get('start_date');
                $end_date = Request::get('end_date');
                $dates = DateTime::rangeDates($start_date,$end_date);
                foreach ($dates as $date){
                    $xAxis[] = $date;
                    $series[] = Db::name($table)->whereDay($this->dateField, $date)->$type($field);
                }
                break;
        }
        $this->chart->xAxis($xAxis)->series($name, $series);
    }

    public function view()
    {
        $html = $this->chart->render();
        $html = "<template>{$html}</template>";
        $moudel = app('http')->getName();
        $node = $moudel . '/' . request()->pathinfo();
        $this->setVar('url', $node);
        if (Request::has('ajax')) {
            return $html;
        }
        $this->setVar('html', rawurlencode($html));
        return parent::render(); // TODO: Change the autogenerated stub
    }
}