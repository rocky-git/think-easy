<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-18
 * Time: 00:18
 */

namespace thinkEasy\grid;


class Actions extends Column
{
    //隐藏详情按钮
    protected $hideDetailButton = false;
    //隐藏编辑按钮
    protected $hideEditButton = false;
    //隐藏删除按钮
    protected $hideDelButton = false;

    protected $closure = null;
    protected $detailButton = '<el-button size="small" icon="el-icon-info" @click="handleDetail(data,index)" data-title="详情">详情</el-button>';
    protected $editButton = '<el-button type="primary" size="small" icon="el-icon-edit" @click="handleEdit(data,index)" data-title="编辑" >编辑</el-button>';
    protected $delButton = '<el-button type="danger" size="small" icon="el-icon-delete" @click="handleDelete(data,index)" >删除</el-button>';

    protected $prependArr = [];

    protected $appendArr = [];

    public function __construct(string $field = '', string $label = '')
    {
        parent::__construct($field, $label);
        $this->setAttr('fixed', 'right');

    }

    public function setClosure(\Closure $closure)
    {
        $this->closure = $closure;
    }

    //隐藏详情按钮
    public function hideDetail()
    {
        $this->hideDetailButton = true;
    }

    //隐藏编辑按钮
    public function hideEdit()
    {
        $this->hideEditButton = true;
    }

    //隐藏删除按钮
    public function hideDel()
    {
        $this->hideDelButton = true;
    }

    /**
     * 前面追加
     * @param $val
     */
    public function prepend($val)
    {
        $this->prependArr[] = $val;
    }

    /**
     * 追加尾部
     * @param $val
     */
    public function append($val)
    {
        $this->appendArr[] = $val;
    }

    /**
     * 设置数据
     * @param $data 行数据
     */
    public function setData($data)
    {
        if (!is_null($this->closure)) {
            if(!empty($data)){
                call_user_func_array($this->closure, [$this, $data]);
            }
        }
        $html = '';
        if (!$this->hideDetailButton) {
            $html .= $this->detailButton;
        }
        if (!$this->hideEditButton) {
            $html .= $this->editButton;
        }
        if (!$this->hideDelButton) {
            $html .= $this->delButton;
        }
        foreach ($this->prependArr as $val) {
            $html = $val . $html;
        }
        foreach ($this->appendArr as $val) {
            $html .= $val;
        }
        $this->appendArr = [];
        $this->prependArr = [];
        $this->display(function () use ($html) {
            return $html;
        });
        parent::setData($data);
        $this->hideDetailButton = false;
        $this->hideEditButton = false;
        $this->hideDelButton = false;
    }


}
