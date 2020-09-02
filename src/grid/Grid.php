<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-14
 * Time: 23:27
 */

namespace thinkEasy\grid;


use think\facade\Db;
use think\model\relation\BelongsTo;
use think\model\relation\BelongsToMany;
use think\model\relation\HasMany;
use think\model\relation\HasOne;
use thinkEasy\facade\Button;
use thinkEasy\form\Dialog;
use think\facade\Request;
use think\Model;
use thinkEasy\service\AdminService;
use thinkEasy\service\TokenService;
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
    //排序字段
    protected $sortField = null;
    //导出数据
    protected $exportData = [];
    //导出文件名
    protected $exportFileName = null;
    protected $relations = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->db = $this->model->db();
        $this->tableFields = $this->model->getTableFields();
        $this->actionColumn = new Actions('actionColumn', '');
        $this->table = new Table($this->columns, []);
        $this->table->setAttr(':max-height', 'tableHeight');
        $this->table->setAttr('style', 'z-index:0');
        if (in_array($this->softDeleteField, $this->tableFields)) {
            $this->isSotfDelete = true;
            if (request()->has('is_deleted')) {
                $this->db->removeWhereField($this->softDeleteField);
                $this->db->whereNotNull($this->softDeleteField);
            } else {
                $this->db->whereNull($this->softDeleteField);
            }
            $this->trashed(true);
        }
        $this->table->setVar('grid', true);
    }

    /**
     * 拖拽排序列
     * @param $field 排序字段
     * @param string $label 显示标签
     */
    public function sortDrag($field = 'sort', $label = '排序')
    {
        $this->sortField = $field;
        $this->model()->order($field);
        $this->column($field, $label)->display(function ($val, $data) {
            $html = <<<EOF
<div style="text-align:center;">
<el-tooltip  effect="dark" content="置顶" placement="right-start"><i @click="sortTop(index,data)" class="el-icon-caret-top" style="cursor: pointer"></i></el-tooltip>
<el-tooltip effect="dark" content="拖动排序" placement="right-start"><i class="el-icon-rank sortHandel" style="font-weight:bold;cursor: grab"></i></el-tooltip>
<el-tooltip  effect="dark" content="置底" placement="right-start"><i @click="sortBottom(index,data)" class="el-icon-caret-bottom" style="cursor: pointer"></i></el-tooltip>
</div>
EOF;
            return $html;
        })->width(50)->align('center');
    }

    /**
     * input排序列
     * @param string $field 排序字段
     * @param string $label 显示标签
     */
    public function sortInput($field = 'sort', $label = '排序')
    {
        $this->sortField = $field;
        $this->column('sort', $label)->display(function ($val, $data) use ($field) {
            $html = <<<EOF
<el-input v-model="data.{$field}" @input="sortInput(data,'{$field}',data.{$field})"></el-input>
EOF;
            return $html;
        })->width(80)->align('center');
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
     * 设置添加按钮参数
     * @Author: rocky
     * 2019/11/27 16:50
     * @param $params 格式：['id'=>1,'a'=>2]
     */
    public function setAddButtonParam(array $params)
    {
        $this->table->setVar('addButtonParam', $params);
    }

    /**
     * 对话框表单
     * @param $fullscreen 是否全屏
     */
    public function setFormDialog($fullscreen = false, $width = '40%')
    {
        $this->table->setFormDialog('', $fullscreen, $width);
    }

    /**
     * 快捷搜索
     */
    public function quickSearch()
    {
        $this->table->setVar('quickSearch', true);
    }

    /**
     * 对话框表单
     * @param $fullscreen 是否全屏
     */
    public function setFormDrawer($direction = 'rtl', $size = '30%')
    {
        $this->table->setFormDrawer('', $direction, $size);
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
        return $column->display(function ($val, $data) use ($column, $nickname) {
            $nicknameValue = $column->getValue($data, $nickname);
            return "<el-image style='width: 80px; height: 80px;border-radius: 50%' src='{$val}' fit='fit' :preview-src-list='[\"{$val}\"]' lazy></el-image><br>{$nicknameValue}";
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
        $fields = explode('.', $field);
        if (count($fields) > 1) {
            $this->relations[] = array_shift($fields);
        }
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
        if (count($this->data) > 0) {
            foreach ($this->data as $key => &$rows) {
                foreach ($this->columns as $column) {
                    $field = $column->getField();
                    $column->setData($rows);
                    $this->exportData[$key][$column->field] = $column->getExportValue();
                    if ($column->isTotal()) {
                        $rows[$field . 'isTotalRow'] = true;
                        $rows[$field . 'totalText'] = $column->totalText;
                        $this->table->setAttr('show-summary', true);
                        $this->table->setAttr(':summary-method', 'columnSumHandel');
                    }
                }
            }
        } else {
            foreach ($this->columns as $column) {
                $column->setData([]);
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
            call_user_func($this->beforeUpdate, $ids, $data);
        }
        $action = isset($data['action']) ? $data['action'] : '';
        if ($action == 'buldview_drag_sort') {
            $sortable_data = $data['sortable_data'];
            $field = "id,(@rownum := @rownum+1),case when @rownum = {$sortable_data['sort']} then @rownum := @rownum+1 else @rownum := @rownum end AS rownum";
            $sortSql = $this->model->table("(SELECT @rownum := -1) r," . $this->model->getTable())
                ->fieldRaw($field)
                ->removeOption('order')
                ->order($this->sortField)
                ->where('id', '<>', $sortable_data['id'])
                ->buildSql();
            $this->model->where($this->model->getPk(), $sortable_data['id'])->update([$this->sortField => $sortable_data['sort']]);
            $res = Db::execute("update {$this->model->getTable()} inner join {$sortSql} a on a.id={$this->model->getTable()}.id set {$this->sortField}=a.rownum");
        } else {
            return $this->model->removeWhereField($this->softDeleteField)->strict(false)->whereIn($this->model->getPk(), $ids)->update($data);
        }
    }

    /**
     * 隐藏工具栏
     */
    public function hideTools()
    {
        $this->table->setVar('hideTools', true);
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
    public function updateing(\Closure $closure)
    {
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
            $this->db->removeWhereField($this->softDeleteField);
            if ($ids === true) {
                if ($this->isSotfDelete && !$trueDelete) {
                    $res = $this->db->where('1=1')->update([$this->softDeleteField => date('Y-m-d H:i:s')]);
                } else {
                    if (in_array($this->softDeleteField, $this->tableFields)) {
                        $deleteDatas = $this->db->whereNotNull($this->softDeleteField)->select();
                        $this->deleteRelationData($deleteDatas);
                        $res = $this->db->whereNotNull($this->softDeleteField)->delete();
                    } else {
                        $deleteDatas = $this->model->select();
                        $this->deleteRelationData($deleteDatas);
                        $res = $this->db->where('1=1')->delete();
                    }
                }
            } else {
                if ($this->isSotfDelete && !$trueDelete) {
                    $res = Db::name($this->model->getTable())->whereIn($this->model->getPk(), $ids)->update([$this->softDeleteField => date('Y-m-d H:i:s')]);
                } else {
                    if ($ids === true) {
                        $this->deleteRelationData(true);
                    } else {
                        $deleteDatas = $this->model->removeOption('where')->whereIn($this->model->getPk(), $ids)->select();
                        $this->deleteRelationData($deleteDatas);
                    }
                    $res = Db::name($this->model->getTable())->delete($ids);
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

    protected function getDataArray()
    {
        return $this->data->toArray();
    }

    /**
     * 开启导出
     * @param $fileName 导出文件名
     */
    public function export($fileName = '')
    {
        $this->table->setVar('exportOpen', true);
        $moudel = app('http')->getName();
        $node = $moudel . '/' . request()->pathinfo();
        $token = TokenService::instance()->get();
        $params = http_build_query(request()->param());

        $this->table->setVar('exportUrl', request()->domain() . '/' . $node . '?Authorization=' . rawurlencode($token) . '&' . $params);
        $this->exportFileName = empty($fileName) ? date('Ymd') : $fileName;
    }

    //导出数据操作
    protected function exportData()
    {
        if (Request::get('build_request_type') == 'export') {
            foreach ($this->columns as $column) {
                $field = $column->field;
                if (!$column->closeExport && !empty($field && $field != 'actionColumn')) {
                    $columnTitle[$field] = $column->label;
                }
            }
            if (Request::get('export_type') == 'all') {
                set_time_limit(0);
                $this->db->chunk(500, function ($datas) use ($columnTitle) {
                    $this->data = $datas;
                    $this->parseColumn();
                    Excel::export($columnTitle, $this->exportData, $this->exportFileName);
                    $this->exportData = [];
                });

                exit;
            } elseif (Request::get('export_type') == 'select') {
                $this->data = $this->model->whereIn($this->model->getPk(), Request::get('ids'))->select();
            }
            $this->parseColumn();

            Excel::export($columnTitle, $this->exportData, $this->exportFileName);
            exit;
        }
    }

    private function permissionCheck()
    {
        $pathinfo = Request::pathinfo();
        $moudel = app('http')->getName();
        $node = $moudel . '/' . $pathinfo;
        //添加权限判断
        if (!AdminService::instance()->check($node . '.rest', 'post')) {
            $this->hideAddButton();
        }
        //删除权限判断
        if (!AdminService::instance()->check($node . '/:id.rest', 'delete')) {
            $this->hideDeleteButton();
        }
    }
    protected function quickFilter()
    {
        $keyword = Request::get('quickSearch');
        //快捷搜索
        $relationWhereSqls = [];
        foreach ($this->relations as $relation) {
            $sql = $this->model->hasWhere($relation)->buildSql();
            $relation = $this->model->$relation();
            $tableFields = $relation->getTableFields();
            $fields = implode('|', $tableFields);
            $relation_table = $relation->getTable();
            $sqlArr = explode('ON ', $sql);
            $str = array_pop($sqlArr);
            preg_match_all("/`(.*)`/U", $str, $arr);
            if ($relation instanceof BelongsTo || $relation instanceof HasMany) {
                $foreignKey = $arr[1][1];
                $pk = $arr[1][3];
            }
            if ($relation instanceof HasOne) {
                $pk = $arr[1][1];
                $foreignKey = $arr[1][3];
            }
            $db = null;
            if ($relation instanceof HasMany) {
                $db = $relation->whereRaw("{$relation_table}.{$pk}={$this->db->getTable()}.{$foreignKey}");
            } elseif ($relation instanceof BelongsTo) {
                $db = $relation->whereRaw("{$pk}={$this->db->getTable()}.{$foreignKey}");
            } else if ($relation instanceof HasOne) {
                $db = $relation->whereRaw("{$foreignKey}={$this->db->getTable()}.{$pk}");
            }
            if($db){
                $sql = $db->whereLike($fields, "%{$keyword}%")->buildSql();
                $relationWhereSqls[] = $sql;
            }

        }
        $fields = implode('|', $this->tableFields);
        $this->db->where(function ($q) use($relationWhereSqls,$fields,$keyword){
            $q->whereLike($fields, "%{$keyword}%",'OR');
            foreach ($relationWhereSqls as $sql){
                $q->whereExists($sql, 'OR');
            }
        });
    }

    /**
     * 视图渲染
     */
    public function view()
    {
        //快捷搜索
        if (Request::has('quickSearch')) {
            $this->quickFilter();
        }
        //排序
        if (Request::has('sort_field')) {
            $this->db->removeOption('order')->order(Request::get('sort_field'), Request::get('sort_by'));
        }
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
                $this->column($this->softDeleteField, '删除时间');
                $this->hideAction();
                $this->column('action_delete', '操作')->display(function ($val, $data) {
                    $button = Button::create('恢复数据', '', 'small', 'el-icon-zoom-in')
                        ->delete($data['id'], '此操作将恢复该数据, 是否继续?', 2);
                    $button .= Button::create('永久删除', 'danger', 'small', 'el-icon-delete')
                        ->delete($data['id'], '此操作将永久删除该数据, 是否继续?', 1);
                    return $button;
                });
            } else {
                $this->column($this->softDeleteField, '删除时间')->setAttr('v-if', 'deleteColumnShow');

            }
        }
        //如果是导出数据
        $this->exportData();
        //权限控制按钮
        $this->permissionCheck();
        //解析列
        $this->parseColumn();
        $this->table->setAttr('data', $this->getDataArray());
        $this->table->setAttr('row-key', $this->model->getPk());
        //查询过滤
        if (!is_null($this->filter)) {
            $this->table->setVar('filter', $this->filter->render());
            $this->table->setScriptArr($this->filter->scriptArr);
        }
        //树形
        if ($this->treeTable) {
            $treeData = $this->tree($this->getDataArray());
            $this->data = $treeData;

            $this->table->setAttr('data', $treeData);
            $this->table->setAttr('default-expand-all', true);
            $this->table->setAttr('tree-props', [
                'children' => 'children',
                'hasChildren' => 'hasChildren',
            ]);
        }
        $build_request_type = Request::get('build_request_type');
        $submitUrl = app('http')->getName() . '/' . request()->controller();
        $submitUrl = str_replace('.rest', '', $submitUrl);
        $this->table->setVar('submitUrl', $submitUrl);
        $this->table->setVar('submitParams', request()->param());

        switch ($build_request_type) {
            case 'page':
                $this->table->view();
                $result['data'] = $this->data;
                $result['total'] = $this->db->removeOption('page')->removeOption('limit')->count();
                $result['cellComponent'] = $this->table->cellComponent();
                return $result;
                break;
            default:
                return $this->table->view();
        }
    }
}
