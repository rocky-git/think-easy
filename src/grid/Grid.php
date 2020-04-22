<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-14
 * Time: 23:27
 */

namespace thinkEasy\grid;


use thinkEasy\form\Dialog;
use think\facade\Request;
use think\Model;
use thinkEasy\View;

class Grid extends View
{
    //当前模型
    protected $model;

    //列
    protected $columns = [];

    //数据
    protected $data = [];

    //表字段
    protected $tableFields = [];

    //表格组件
    protected $table;

    //是否开启树形表格
    protected $treeTable = false;

    //是否开启分页
    protected $isPage = true;

    //分页大小
    protected $pageLimit = 20;

    //隐藏操作列
    protected $hideAction = false;

    //操作列
    protected $actionColumn;

    //树形上级id
    protected $treeParent = 'pid';

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->tableFields = $this->model->getTableFields();
        $this->actionColumn = new Actions('id','操作');
        $this->table = new Table($this->columns, []);
    }

    /**
     * 返回表格组件，可设置属性
     * @return Table
     */
    public function table(){
        return $this->table;
    }

    /**
     * 对话框表单
     */
    public function setFormDialog(){
        $this->table->setFormDialog('');
    }
    /**
     * 开启树形表格
     * @param string $pid 父级字段
     */
    public function treeTable($pidField = 'pid')
    {
        $this->treeParent = $pidField;
        $this->isPage = false;
        $this->treeTable = true;
    }

    protected function tree($list, $pid = 0)
    {
        $tree = [];
        if (!empty($list)) {
            $newList = [];
            foreach ($list as $k => $v) {
                $newList[$v['id']] = $v;
            }
            foreach ($newList as $value) {
                if ($pid == $value[$this->treeParent]) {
                    $tree[] = &$newList[$value['id']];
                } elseif (isset($newList[$value[$this->treeParent]])) {
                    $newList[$value[$this->treeParent]]['children'][] = &$newList[$value['id']];
                }
            }
        }
        return $tree;
    }

    /**
     * 设置标题
     * @param $title
     */
    public function setTitle($title){
        $this->title = $title;
        $this->table->setVar('title',$title);
    }
    /**
     * 操作列定义
     * @param \Closure $closure
     */
    public function actions(\Closure $closure)
    {
        $this->actionColumn->setClosure($closure);
    }
    /**
     * 隐藏操作列
     */
    public function hideAction()
    {
        $this->hideAction = true;
    }
    /**
     * 设置分页每页限制
     * @Author: rocky
     * 2019/11/6 14:01
     * @param $limit
     */
    public function setPageLimit($limit)
    {
        $this->pageLimit = $limit;
    }

    /**
     * 关闭分页
     */
    public function hidePage()
    {
        $this->isPage = false;
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

    /**
     * 设置索引列
     * @param string $type 列类型：selection 多选框 ， index 索引 ， expand 可展开的
     * @return Column
     */
    public function indexColumn($type='selection'){
        $column = $this->column('','');
        $column->setAttr('type',$type);
        return $column;
    }
    /**
     * 解析列
     */
    protected function parseColumn()
    {
        //是否隐藏操作列
        if(!$this->hideAction){
            array_push($this->columns,$this->actionColumn);
        }
        foreach ($this->data as $key => &$rows) {
            foreach ($this->columns as $column) {
                $column->setData($rows);
            }
        }
        $this->table->setColumn($this->columns);
    }

    /**
     * 隐藏删除按钮
     */
    public function hideDeleteButton()
    {
        $this->table->setVar('hideDeletesButton', true);
    }
    /**
     * 删除数据
     */
    public function destroy($id){
        if($id == 'delete'){
            $ids = Request::delete('ids');
            if($ids == 'true'){
                return $this->model->where('1=1')->delete();
            }
        }else{
            $ids = explode(',', $id);
        }
        return $this->model->destroy($ids);
    }
    /**
     * 视图渲染
     */
    public function view()
    {
        //分页
        if ($this->isPage) {
            $this->table->setVar('pageHide', 'false');
            $count = $this->model->count();
            $this->table->setVar('pageSize', $this->pageLimit);
            $this->table->setVar('pageTotal', $count);
            $this->data = $this->model->page(Request::get('page', 1), Request::get('size', $this->pageLimit))->select()->toArray();
        } else {
            $this->data = $this->model->select()->toArray();
        }
        //解析列
        $this->parseColumn();

        $this->table->setAttr('data', $this->data);
        //树形
        if ($this->treeTable) {
            $this->data = $this->tree($this->data);
            $this->table->setAttr('row-key', $this->model->getPk());
            $this->table->setAttr('data', $this->data);
            $this->table->setAttr('default-expand-all', true);
            $this->table->setAttr('tree-props', [
                'children' => 'children',
                'hasChildren' => 'hasChildren',
            ]);
        }
        $build_request_type = Request::get('build_request_type');
        switch ($build_request_type) {
            case 'page':
                if (!$this->treeTable) {
                    $this->data = $this->model->page(Request::get('page', 1), Request::get('size', $this->pageLimit))->select();
                }
                $this->table->view();
                $result['data'] =$this->data;
                $result['total'] = $this->model->count();
                $result['cellComponent'] = $this->table->cellComponent();
                return $result;
                break;
            default:
                return $this->table->view();
        }


    }
}
