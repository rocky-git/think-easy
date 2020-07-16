<?php


namespace thinkEasy\grid;


use think\exception\HttpResponseException;
use think\facade\Request;
use think\Model;
use think\model\relation\HasMany;
use thinkEasy\layout\Card;
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
    protected $component=[];
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
     * 布局
     * @param $title 标题
     * @param $md 占列
     * @param \Closure $closure
     * @return $this
     */
    public function layout($title, $md, \Closure $closure)
    {
        array_push($this->columns, ['type' => 'layout', 'title' => $title, 'md' => $md, 'closure' => $closure]);
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
     * 一对多
     * @param $relationMethod 一对多方法
     * @param $title 标题
     * @param $md 占列
     * @param \Closure $closure
     * @return $this
     */
    public function hasMany($relationMethod,$title, $md,\Closure $closure)
    {
        if (method_exists($this->model, $relationMethod)) {
            if($this->model->$relationMethod() instanceof HasMany){
                array_push($this->columns, ['type' => 'hasMany', 'title' => $title, 'md' => $md,'relationMethod'=>$relationMethod,  'closure' => $closure]);
            }else{
                abort(999,'关联方法不是一对多');
            }
        }else{
            abort(999,'无效关联方法');
        }
        return $this;
    }
    /**
     * 解析一对多
     * @Author: rocky
     * 2019/8/1 15:00
     * @param $relationMethod 一对多关联方法
     * @return string\
     */
    private function parsehasManData($relationMethod){
        foreach ($this->data->$relationMethod as $rowIndex=>$val){
            foreach ($this->columns as $column) {
                $column->setData($val);
            }
        }
        $table = new Table($this->columns, $this->data->$relationMethod->toArray());
        return $table->view();
    }

    /**
     * 解析布局
     * @param $title 标题
     * @param $md 占列
     * @return string
     */
    private function paseLayout($title,$md){
        $card = new Card();
        $card->header($title);
        $html = '';
        foreach ($this->columns as $i=>$column) {
            $column->setData($this->data);
            $this->cellComponent[] = $column->getDetailDisplay($i);
            $this->scriptArr = array_merge($this->scriptArr, $column->getScriptVar());
            $html .= $column->detailRender();
        }
        $card->body($html);
        return "<el-col :span='{$md}'>{$card->render()}</el-col>";
    }
    public function view(){
        $columnHtml = '';
        $manyColumnHtml = '';
        foreach ($this->columns as $i=>$column) {
            if($column instanceof Column){
                $column->setData($this->data);
                $this->cellComponent[] = $column->getDetailDisplay($i);
                $columnHtml .= $column->detailRender();
                $this->scriptArr = array_merge($this->scriptArr, $column->getScriptVar());
            }elseif($column['type'] == 'hasMany'){
                $columnsArr = array_slice($this->columns, $i + 1);
                $this->columns = [];
                $card = new Card();
                call_user_func($column['closure'], $this);
                $component = $this->parsehasManData($column['relationMethod']);
                $componentKey = 'component'.mt_rand(10000,99999);
                $this->component[$componentKey] = "() => new Promise(resolve => {
                            resolve(this.\$splitCode(decodeURIComponent('".rawurlencode($component)."')))
                        })";
                $card->header($column['title']);
                $card->body('<component :is="'.$componentKey.'" />');
                $manyColumnHtml .= "<el-col :span='{$column['md']}'>{$card->render()}</el-col>";

                $this->columns = $columnsArr;
            }elseif($column['type'] == 'layout'){
                $columnsArr = array_slice($this->columns, $i + 1);
                $this->columns = [];
                call_user_func($column['closure'], $this);
                $manyColumnHtml .= $this->paseLayout($column['title'],$column['md']);
                $this->columns = $columnsArr;
            }
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
        foreach ($this->component as $key=>$value){
            $scriptVar .= "$key:$value,";
        }
        $this->setVar('html', $columnHtml);
        $this->setVar('manyColumnHtml', $manyColumnHtml);
        $this->setVar('scriptVar', $scriptVar);
        return $this->render();
    }
}
