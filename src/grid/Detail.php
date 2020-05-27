<?php


namespace thinkEasy\grid;


use think\exception\HttpResponseException;
use think\facade\Request;
use think\Model;
use thinkEasy\View;

class Detail extends View
{
    //当前模型
    protected $model;

    //当前模型的数据库查询对象
    protected $db;

    //数据
    protected $data = [];

    //表字段
    protected $tableFields = [];

    //列
    protected $columns = [];
    protected $cellComponent;
    protected $scriptArr = [];
    public function __construct(Model $model)
    {
        $this->template = 'detail';
        $this->model = $model;
        $this->db = $this->model->db();
        $this->tableFields = $this->model->getTableFields();
        $this->setTitle('详情');
    }

    /**
     * 设置详情数据
     * @param $id 详情id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detailData($id){
        if ($id) {
            $this->id = $id;
            $this->data = $this->model->find($id);
            if (empty($this->data)) {
                throw new HttpResponseException(json(['code' => 0, 'message' => '数据不存在！', 'data' => []]));
            }
        }
        return $this;
    }
    /**
     * 设置标题
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->setVar('title', $title);
    }
    /**
     * 设置列
     * @Author: rocky
     * 2019/7/25 16:20
     * @param $field 字段
     * @param $label 标签
     * @return Column
     */
    public function column($field, $label)
    {
        $column = new Column($field, $label);
        array_push($this->columns, $column);
        return $column;
    }

    public function view(){
        $columnHtml = '';
        foreach ($this->columns as $i=>$column) {
            $column->setData($this->data);
            $this->cellComponent[] = $column->getDetailDisplay($i);
            $columnHtml .= $column->detailRender();
            $this->scriptArr = array_merge($this->scriptArr, $column->getScriptVar());
        }
        $columnScriptVar = implode(',', $this->scriptArr);
        list($attrStr, $scriptVar) = $this->parseAttr();
        if (!empty($columnScriptVar)) {
            $scriptVar = $scriptVar . ',' . $columnScriptVar;
        }
        if (Request::has('build_dialog')) {
            $this->setVar('title', '');
        }
        $this->setVar('data',json_encode($this->data,JSON_UNESCAPED_UNICODE));
        $this->setVar('cellComponent', json_encode($this->cellComponent, JSON_UNESCAPED_UNICODE));
        $this->setVar('html', $columnHtml);
        $this->setVar('scriptVar', $scriptVar);
        return $this->render();
    }
}
