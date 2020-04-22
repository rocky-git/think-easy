<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-21
 * Time: 19:24
 */

namespace thinkEasy\form;


use thinkEasy\View;

class Field extends View
{
    //字段
    public $field = '';

    //标签
    public $label = '';

    //验证规则
    public $rule = [];

    //占位栅格数
    public $md = 0;

    //输入框inline
    protected $inline = '';
    /**
     * Input constructor.
     * @param $field 字段
     * @param $label 标签
     */
    public function __construct($field, $label)
    {
        $this->field = $field;
        $this->label = $label;
        $this->rule = json_encode([], JSON_UNESCAPED_UNICODE);
        $this->setAttr('v-model', 'form.' . $field);
        $this->setAttr('placeholder', '请输入' . $label);
    }
    /**
     * 禁用
     */
    public function disabled()
    {
        $this->setAttr('disabled', 'true');
        return $this;
    }
    /**
     * 必填
     * @return $this
     */
    public function required()
    {
        $this->rule = json_encode([['required' => true, 'message' => '请输入' . $this->label]], JSON_UNESCAPED_UNICODE);
        return $this;
    }
    /**
     * 输入框inline
     */
    public function inline()
    {
        $this->inline = "<el-col :span='3'>%s</el-col>";
        return $this;
    }

    /**
     * 输入框占位提示文本
     */
    public function placeholder($text)
    {
        $this->setAttr('placeholder', $text);
        return $this;
    }
    /**
     * 占位栅格数，24栏占满
     * @param $num 数量
     * @return $this
     */
    public function md($num = 3)
    {
        $this->md = "<el-col :span='{$num}'>%s</el-col>";
        return $this;
    }
}
