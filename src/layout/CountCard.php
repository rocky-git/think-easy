<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-06-22
 * Time: 21:49
 */

namespace thinkEasy\layout;


use thinkEasy\View;

/**
 * 统计卡片
 * Class CountCard
 * @package thinkEasy\layout
 */
class CountCard extends View
{
    /**
     * @param string $title 标题
     * @param int $currentTotal 当前统计数量
     * @param int $total    总统计数量
     * @param string $icon 图标
     * @param string $iconColor 图标颜色
     * @param string $badge 右上的描述
     * @param string $badgeType 描述背景色
     * @return $this
     */
    public function create($title, $currentTotal, $total, $icon, $iconColor = '',$badge ='日',$badgeType='primary')
    {
        if($total == 0){
            $percentage = 0;
        }else{
            $percentage = $currentTotal / $total * 100;
        }
        $this->html = "<div style='display: flex;flex-direction: column;position: relative'><el-badge type='{$badgeType}' value='{$badge}' style='margin-left: auto;top: -8px;position: absolute;right: -2px'></el-badge>
<div style='display: flex;justify-content: space-between;align-items: center;margin: 5px 0px;'><i class='$icon' style='font-size: 55px;color: {$iconColor}'></i><div style='display: flex;flex-direction: column;align-items: flex-end'><span style='font-size: 12px;color: #666;margin-top: 15px'>{$title}</span><span style='font-size: 26px;font-weight: bold;align-items: normal;'><count-to :start-val='0' :decimals='{$this->getFloatLength($currentTotal)}' :end-val='{$currentTotal}'  /></span></div></div>
<el-progress :show-text=\"false\" :stroke-width='5' color='{$iconColor}' :percentage='{$percentage}'></el-progress>
<div style='margin-top: 5px;display: flex;justify-content: space-between;align-items: flex-end'><span style='font-size: 12px;color: #666;'>总{$title}</span> <span style='font-size: 14px'><count-to :start-val='0' :decimals='{$this->getFloatLength($total)}' :end-val='{$total}'  /></span></div></div>";
        return $this->render();
    }
    protected function getFloatLength($num){
        $count = 0;
        $temp = explode ( '.', $num );
        if (count ( $temp ) > 1) {
            $decimal = end ( $temp );
            $count = strlen ( $decimal );
        }
        return $count;
    }

    public function render()
    {
        return "<el-card shadow='hover' style='cursor: pointer;'>{$this->html}</el-card>";
    }
}
