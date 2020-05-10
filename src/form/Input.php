<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-19
 * Time: 09:48
 */

namespace thinkEasy\form;




class Input extends Field
{

    protected $prefixHtml = '';
    protected $suffixHtml = '';
    protected $prependHtml = '';
    protected $appendHtml = '';
    protected $attrs = [
        'show-password',
        'disabled',
        'readonly',
    ];


    /**
     * 输入框头部图标
     * @param $icon
     */
    public function prefixIcon($icon)
    {
        $this->setAttr('prefix-icon', $icon);
        return $this;
    }

    /**
     * 输入框前置内容，只对 type="text" 有效
     * @param $html
     */
    public function prepend($html)
    {
        $this->prependHtml = "<template slot='prepend'>{$html}</template>";
        return $this;
    }

    /**
     * 输入框后置内容，只对 type="text" 有效
     * @param $html
     */
    public function append($html)
    {
        $this->appendHtml = "<template slot='append'>{$html}</template>";
        return $this;
    }

    /**
     * 输入框头部内容，只对 type="text" 有效
     * @param $html
     */
    public function prefix($html)
    {
        $this->prefixHtml = "<template slot='prefix'>{$html}</template>";
        return $this;
    }

    /**
     * 输入框尾部内容，只对 type="text" 有效
     * @param $html
     */
    public function suffix($html)
    {
        $this->suffixHtml = "<template slot='suffix'>{$html}</template>";
        return $this;
    }

    /**
     * 输入框的tabindex
     * @param $num
     * @return $this
     */
    public function tabindex($num)
    {
        $this->setAttr('tabindex', $num);
        return $this;
    }

    /**
     * 自动获取焦点
     * @return $this
     */
    public function autofocus()
    {
        $this->setAttr('autofocus', 'true');
        return $this;
    }

    /**
     * 控制是否能被用户缩放
     * @param $type  none, both, horizontal, vertical
     */
    public function resize($type)
    {
        $this->setAttr('resize', $type);
        return $this;
    }

    /**
     * 设置输入字段的合法数字间隔
     * @param $num
     * @return $this
     */
    public function step($num)
    {
        $this->setAttr('mistepn', $num);
        return $this;
    }

    /**
     * 设置最大值
     * @param $num
     * @return $this
     */
    public function max($num)
    {
        $this->setAttr('min', $num);
        return $this;
    }

    /**
     * 设置最小值
     * @param $num
     * @return $this
     */
    public function min($num)
    {
        $this->setAttr('min', $num);
        return $this;
    }

    /**
     * 只读
     * @return $this
     */
    public function readonly()
    {
        $this->setAttr('readonly', true);
        return $this;
    }

    /**
     * textarea输入框行数
     * @param $num
     * @return $this
     */
    public function rows($num)
    {
        $this->setAttr('rows', $num);
        return $this;
    }

    /**
     * 密码输入框
     */
    public function password(){
        $this->setAttr('show-password',true);
    }
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $input = "<el-input @blur=\"clearValidate('{$this->field}')\" {$attrStr}>%s</el-input>";
        if (!empty($this->inline)) {
            $input = sprintf($this->inline, $input);
        }
        if (!empty($this->prependHtml)) {
            $input = sprintf($input, $this->prependHtml . '%s');
        }
        if (!empty($this->suffixHtml)) {
            $input = sprintf($input, $this->suffixHtml . '%s');
        }
        if (!empty($this->appendHtml)) {
            $input = sprintf($input, $this->appendHtml . '%s');
        }
        if (!empty($this->prefixHtml)) {
            $input = sprintf($input, $this->prefixHtml . '%s');
        }
        $input = sprintf($input, '');
        return $input;
    }
}
