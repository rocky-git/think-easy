<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-06-18
 * Time: 22:38
 */

namespace thinkEasy\form;

/**
 * 评分组件
 * Class Rate
 * @package thinkEasy\form
 */
class Rate extends Field
{
    protected $attrs = [
        'disabled',
        'allow-half',
        'low-threshold',
        'high-threshold',
        'show-text',
        'show-score',
        'vertical',
        'min',
        'max',
    ];

    /**
     * 设置最大值
     * @param $num
     * @return $this
     */
    public function max($num)
    {
        $this->setAttr('max', $num);
        return $this;
    }

    /**
     * 允许半选
     * @return $this
     */
    public function allowHalf()
    {
        $this->setAttr('allow-half', true);
        return $this;
    }

    /**
     * 提示辅助文字
     * @param array $texts 辅助文字数组
     * @return $this
     */
    public function showText(array $texts = [])
    {
        $max = count($texts);
        if ($max == 0) {
            $max = 5;
        } else {
            $this->setAttr(':texts', json_encode($texts));
        }
        $this->max($max);
        $this->setAttr('show-text', true);
        return $this;
    }

    /**
     * 展示分数
     * @return $this
     */
    public function showScore()
    {
        $this->setAttr('show-score', true);
        return $this;
    }

    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<el-rate {$attrStr}></el-rate>";
        return $html;
    }
}