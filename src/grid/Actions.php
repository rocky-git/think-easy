<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-18
 * Time: 00:18
 */

namespace thinkEasy\grid;


use thinkEasy\service\AdminService;

class Actions extends Column
{
    //隐藏详情按钮
    protected $hideDetailButton = false;
    //隐藏编辑按钮
    protected $hideEditButton = false;
    //隐藏删除按钮
    protected $hideDelButton = false;

    protected $closure = null;
    protected $detailButton = '<el-dropdown-item icon="el-icon-info" @click.native="handleDetail(data,index)">详情</el-dropdown-item>';
    protected $editButton = '<el-dropdown-item icon="el-icon-edit" @click.native="handleEdit(data,index)">编辑</el-dropdown-item>';
    protected $delButton = '<el-dropdown-item icon="el-icon-delete" @click.native="handleDelete(data,index)">删除</el-dropdown-item>';

    protected $prependArr = [];

    protected $appendArr = [];
    public $row = [];
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
        $this->row = $data;
        if (!is_null($this->closure)) {
            if(!empty($data)){
                call_user_func_array($this->closure, [$this, $data]);
            }
        }
        $html = '';
        $pathinfo = request()->pathinfo();
        $moudel = app('http')->getName();
        $node = $moudel . '/' . $pathinfo;
        if (!$this->hideDetailButton && AdminService::instance()->check($node.'/:id.rest', 'get')) {
            $html .= $this->detailButton;
        }
        if (!$this->hideEditButton && AdminService::instance()->check($node.'/:id.rest', 'put')) {
            $html .= $this->editButton;
        }
        if (!$this->hideDelButton && AdminService::instance()->check($node.'/:id.rest', 'delete')) {
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
            return '
<el-dropdown>
  <span class="el-dropdown-link">
    <i class="el-icon-more" style="cursor: pointer;padding:0 10px" >
  </span>
  <el-dropdown-menu slot="dropdown">'.$html.'
  </el-dropdown-menu>
</el-dropdown></i>';
        });
        parent::setData($data);
        $this->hideDetailButton = false;
        $this->hideEditButton = false;
        $this->hideDelButton = false;
    }
}
