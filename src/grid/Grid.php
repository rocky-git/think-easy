<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-14
 * Time: 23:27
 */

namespace thinkEasy\grid;


use thinkEasy\facade\Button;
use thinkEasy\form\Dialog;
use think\facade\Request;
use think\Model;
use thinkEasy\View;

class Grid extends View
{
    //当前模型
    protected $model;

    //当前模型的数据库查询对象
    protected $db;
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

    //软删除字段
    protected $softDeleteField= 'delete_time';

    //是否开启软删除
    protected $isSotfDelete = false;

    //删除回调
    protected $beforeDel = null;
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->db = $this->model->db();
        $this->tableFields = $this->model->getTableFields();
        $this->actionColumn = new Actions('id','操作');
        $this->table = new Table($this->columns, []);
        if(in_array($this->softDeleteField,$this->tableFields)){
            $this->isSotfDelete = true;
            if(request()->has('is_deleted')){
                $this->db->whereNotNull($this->softDeleteField);
            }else{
                $this->db->whereNull($this->softDeleteField);
            }
            $this->table->setVar('is_deleted',true);
        }
    }

    /**
     * 返回表格组件，可设置属性
     * @return Table
     */
    public function table(){
        return $this->table;
    }

    /**
     * 获取当前模型的数据库查询对象
     * @return Model
     */
    public function model(){
        return $this->db;
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
     * 更新数据
     * @param $ids 更新条件id
     * @param $data 更新数据
     * @return Model
     */
    public function update($ids,$data){
        return $this->model->whereIn($this->model->getPk(),$ids)->strict(false)->update($data);
    }
    /**
     * 隐藏删除按钮
     */
    public function hideDeleteButton()
    {
        $this->table->setVar('hideDeletesButton', true);
    }
    //删除前回调
    public function deling(\Closure $closure)
    {
        $this->beforeDel = $closure;
    }
    /**
     * 删除数据
     */
    public function destroy($id){
        $trueDelete = Request::delete('trueDelete');
        if($id == 'delete'){
            $ids = Request::delete('ids');
        }else{
            $ids = explode(',', $id);
        }
        if($ids == 'true'){
            $ids = true;
        }
        if (!is_null($this->beforeDel)) {
            call_user_func($this->beforeDel, $ids);
        }
        if($ids === true){
            if($this->isSotfDelete && !$trueDelete){
                return $this->model->where('1=1')->update([$this->softDeleteField=>date('Y-m-d H:i:s')]);
            }else{
                return $this->model->whereNotNull($this->softDeleteField)->delete();
            }
        }
        if($this->isSotfDelete && !$trueDelete){
            return $this->model->whereIn($this->model->getPk(),$ids)->update([$this->softDeleteField=>date('Y-m-d H:i:s')]);
        }else{
            return $this->model->destroy($ids);
        }
    }
    /**
     * 视图渲染
     */
    public function view()
    {
        //分页
        if ($this->isPage) {
            $this->table->setVar('pageHide', 'false');
            $count = $this->db->count();
            $this->table->setVar('pageSize', $this->pageLimit);
            $this->table->setVar('pageTotal', $count);
            $this->data = $this->db->page(Request::get('page', 1), Request::get('size', $this->pageLimit))->select()->toArray();
        } else {
            $this->data = $this->db->select()->toArray();
        }
        //软删除列
        if($this->isSotfDelete){
            if(request()->has('is_deleted')){
                $this->db->whereNotNull($this->softDeleteField);
                $this->column($this->softDeleteField,'删除时间');
                $this->hideAction();
                $this->column('action_delete','操作')->display(function ($val,$data){
                    $button = Button::create('恢复数据','','small','el-icon-zoom-in')
                        ->delete($data['id'],'此操作将恢复该数据, 是否继续?',2)->render();
                    $button .= Button::create('永久删除','danger','small','el-icon-delete')
                        ->delete($data['id'],'此操作将永久删除该数据, 是否继续?',1)->render();
                    return $button;
                });
            }else{
                $this->db->whereNull($this->softDeleteField);
                $this->column($this->softDeleteField,'删除时间')->setAttr('v-if','deleteColumnShow');

            }
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
                if (!$this->treeTable && $this->isPage) {
                    $this->data = $this->db->page(Request::get('page', 1), Request::get('size', $this->pageLimit))->select();
                }
                $this->table->view();
                $result['data'] =$this->data;
                $result['total'] = $this->db->count();
                $result['cellComponent'] = $this->table->cellComponent();
                return $result;
                break;
            default:
                return $this->table->view();
        }


    }
}
