<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-14
 * Time: 23:27
 */

namespace thinkEasy\grid;


use think\facade\Db;
use think\model\relation\BelongsToMany;
use think\model\relation\HasOne;
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
    protected $softDeleteField = 'delete_time';

    //是否开启软删除
    protected $isSotfDelete = false;

    //删除前回调
    protected $beforeDel = null;
    //更新前回调
    protected $beforeUpdate = null;
    //是否显示回收站
    protected $trashedShow = false;
    //工具栏
    protected $toolsArr = [];
    //查询过滤
    protected $filter = null;
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->db = $this->model->db();
        $this->tableFields = $this->model->getTableFields();
        $this->actionColumn = new Actions('id', '操作');
        $this->table = new Table($this->columns, []);
        if (in_array($this->softDeleteField, $this->tableFields)) {
            $this->isSotfDelete = true;
            if (request()->has('is_deleted')) {
                $this->db->whereNotNull($this->softDeleteField);
            } else {
                $this->db->whereNull($this->softDeleteField);
            }
            $this->trashed(true);
        }
    }

    /**
     * 是否显示回收站
     * @param $bool true显示，false隐藏
     */
    public function trashed($bool)
    {
        $this->trashedShow = $bool;
        $this->table->setVar('trashed', $this->trashedShow);
    }

    /**
     * 返回表格组件，可设置属性
     * @return Table
     */
    public function table()
    {
        return $this->table;
    }

    /**
     * 获取当前模型的数据库查询对象
     * @return Model
     */
    public function model()
    {
        return $this->db;
    }

    /**
     * 对话框表单
     */
    public function setFormDialog()
    {
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
    //头像昵称列
    public function userInfo($headimg = 'headimg', $nickname = 'nickname', $label = '会员信息')
    {
        $column = $this->column($headimg, $label);
        return $column->display(function ($val, $data) use($column,$nickname){
            $nicknameValue = $column->getValue($data,$nickname);
            return "<el-image style='width: 80px; height: 80px;border-radius: 50%' src='{$val}' fit='fit'></el-image><br>{$nicknameValue}";
        })->align('center');
    }
    /**
     * 设置标题
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->table->setVar('title', $title);
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

    public function addTools($html)
    {
        if ($html instanceof \thinkEasy\form\Button) {
            $this->toolsArr[] = $html->render();
        } else {
            $this->toolsArr[] = $html;
        }
        return $this;
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
    public function indexColumn($type = 'selection')
    {
        $column = $this->column('', '');
        $column->setAttr('type', $type);
        return $column;
    }

    /**
     * 解析列
     */
    protected function parseColumn()
    {
        //是否隐藏操作列
        if (!$this->hideAction) {
            array_push($this->columns, $this->actionColumn);
        }
        foreach ($this->data as $key => &$rows) {
            foreach ($this->columns as $column){
                $column->setData($rows);
            }
        }
        $this->table->setColumn($this->columns);
        $this->table->setVar('toolbar', implode('', $this->toolsArr));
    }

    /**
     * 更新数据
     * @param $ids 更新条件id
     * @param $data 更新数据
     * @return Model
     */
    public function update($ids, $data)
    {
        if (!is_null($this->beforeUpdate)) {
            call_user_func($this->beforeUpdate, $ids,$data);
        }
        return $this->model->update($data,[[$this->model->getPk(),'in', $ids]]);
    }
    /**
     * 隐藏添加按钮
     */
    public function hideAddButton()
    {
        $this->table->setVar('hideAddButton', true);
    }
    /**
     * 隐藏删除按钮
     */
    public function hideDeleteButton()
    {
        $this->table->setVar('hideDeletesButton', true);
    }
    //更新前回调
    public function updateing(\Closure $closure){
        $this->beforeUpdate = $closure;
    }
    //删除前回调
    public function deling(\Closure $closure)
    {
        $this->beforeDel = $closure;
    }

    /**
     * 查询过滤
     * @param $callback
     */
    public function filter($callback)
    {
        if ($callback instanceof \Closure) {
            $this->filter = new Filter($this->db);
            call_user_func($callback, $this->filter);
        }
    }
    /**
     * 删除数据
     */
    public function destroy($id)
    {
        $trueDelete = Request::delete('trueDelete');
        if ($id == 'delete') {
            $ids = Request::delete('ids');
        } else {
            $ids = explode(',', $id);
        }
        if ($ids == 'true') {
            $ids = true;
        }
        if (!is_null($this->beforeDel)) {
            call_user_func($this->beforeDel, $ids, $trueDelete);
        }
        $res = false;
        Db::startTrans();
        try {
            if ($ids === true) {
                if ($this->isSotfDelete && !$trueDelete) {
                    $res = $this->model->where('1=1')->update([$this->softDeleteField => date('Y-m-d H:i:s')]);
                } else {
                    $deleteDatas = $this->model->whereNotNull($this->softDeleteField)->select();
                    $this->deleteRelationData($deleteDatas);
                    $res = $this->model->whereNotNull($this->softDeleteField)->delete();
                }
            } else {
                if ($this->isSotfDelete && !$trueDelete) {
                    $res = $this->model->whereIn($this->model->getPk(), $ids)->update([$this->softDeleteField => date('Y-m-d H:i:s')]);
                } else {
                    if ($ids === true) {
                        $this->deleteRelationData(true);
                    } else {
                        $deleteDatas = $this->model->whereIn($this->model->getPk(), $ids)->select();
                        $this->deleteRelationData($deleteDatas);
                    }
                    $res = $this->model->destroy($ids);
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $res = false;
        }
        return $res;
    }

    /**
     * 删除关联数据
     * @param $deleteDatas
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function deleteRelationData($deleteDatas)
    {
        $reflection = new \ReflectionClass($this->model);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $className = $reflection->getName();
        $relatonMethod = [];
        foreach ($methods as $method) {
            if ($method->class == $className) {
                $relation = $method->name;
                $p = new \ReflectionMethod($method->class, $relation);
                if ($p->getNumberOfParameters() == 0) {
                    if ($this->model->$relation() instanceof BelongsToMany) {
                        if ($deleteDatas === true) {
                            $deleteDatas = $this->model->select();
                        }
                        foreach ($deleteDatas as $deleteData) {
                            $deleteData->$relation()->detach();
                        }
                    } elseif ($this->model->$relation() instanceof HasOne) {
                        if ($deleteDatas === true) {
                            $deleteDatas = $this->model->select();
                        }
                        foreach ($deleteDatas as $deleteData) {
                            if (!is_null($deleteData->$relation)) {
                                $deleteData->$relation->delete();
                            }
                        }
                    }
                }
            }
        }
    }
    protected function getDataArray(){
        return $this->data->toArray();
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
            $this->data = $this->db->page(Request::get('page', 1), Request::get('size', $this->pageLimit))->select();
        } else {
            $this->data = $this->db->select();
        }
        //软删除列
        if ($this->isSotfDelete) {
            if (request()->has('is_deleted')) {
                $this->db->whereNotNull($this->softDeleteField);
                $this->column($this->softDeleteField, '删除时间');
                $this->hideAction();
                $this->column('action_delete', '操作')->display(function ($val, $data) {
                    $button = Button::create('恢复数据', '', 'small', 'el-icon-zoom-in')
                        ->delete($data['id'], '此操作将恢复该数据, 是否继续?', 2)->render();
                    $button .= Button::create('永久删除', 'danger', 'small', 'el-icon-delete')
                        ->delete($data['id'], '此操作将永久删除该数据, 是否继续?', 1)->render();
                    return $button;
                });
            } else {
                $this->db->whereNull($this->softDeleteField);
                $this->column($this->softDeleteField, '删除时间')->setAttr('v-if', 'deleteColumnShow');

            }
        }

        //解析列
        $this->parseColumn();
        $this->table->setAttr('data', $this->getDataArray());
        //查询过滤
        if (!is_null($this->filter)) {
            $this->table->setVar('filter', $this->filter->render());
            $this->table->setScriptArr($this->filter->scriptArr);
        }
        //树形
        if ($this->treeTable) {
            $treeData = $this->tree($this->getDataArray());
            $this->data = $treeData;
            $this->table->setAttr('row-key', $this->model->getPk());
            $this->table->setAttr('data', $treeData);
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
                $result['data'] = $this->data;
                $result['total'] = $this->db->count();
                $result['cellComponent'] = $this->table->cellComponent();
                return $result;
                break;
            default:
                return $this->table->view();
        }
    }
}
