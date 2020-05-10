<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-18
 * Time: 20:32
 */

namespace thinkEasy\form;


use think\exception\HttpResponseException;
use think\facade\Db;
use think\facade\Request;
use think\facade\Validate;
use think\Model;
use think\model\relation\BelongsToMany;
use thinkEasy\View;

/**
 * Class Form
 * @package thinkEasy\form
 * @method \thinkEasy\form\Input text($field, $label) 文本输入框
 * @method \thinkEasy\form\Input textarea($field, $label) 多行文本输入框
 * @method \thinkEasy\form\Input password($field, $label) 密码输入框
 * @method \thinkEasy\form\Input number($field, $label) 数字输入框
 * @method \thinkEasy\form\Select select($field, $label) 下拉选择器
 * @method \thinkEasy\form\Tree tree($field, $label) 树形
 */
class Form extends View
{
    protected $attrs = [
        'model',
        'rules',
        'inline',
        'hide-required-asterisk',
        'show-message',
        'inline-message',
        'status-icon',
        'validate-on-rule-change',
        'disabled',
    ];
    //表单元素组件
    protected $formItem = [];


    protected $tabs = null;


    protected $scriptArr = [];
    //当前模型
    protected $model;

    //表单附加参数
    protected $extraData = [];

    //保存前回调
    protected $beforeSave = null;

    //保存后回调
    protected $afterSave = null;

    protected $data = [];

    protected $formData = ['empty' => 0];

    //是否编辑表单
    protected $isEdit = false;

    //创建验证规则
    protected $createRules = [
        'rule' => [],
        'msg' => [],
    ];
    //更新验证规则
    protected $updateRules = [
        'rule' => [],
        'msg' => [],
    ];
    //表单验证双向绑定变量
    protected $formValidate = [];

    //表字段
    protected $tableFields = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->tableFields = $this->model->getTableFields();
        $this->template = 'form';
        $this->setAttr('label-width', '120px');

        $this->addExtraData([
            'submitFromMethod' => request()->action(),
        ]);
        if (request()->has('id')) {
            $this->edit(request()->param('id'));
        }

    }

    public function __call($name, $arguments)
    {
        return $this->formItem($name, $arguments[0], $arguments[1]);
    }


    /**
     * 表单选项卡标签页
     * @param $label 标签
     * @param \Closure $closure 回调
     * @param string $position 选项卡所在位置 top/right/bottom/left
     * @param string $type 风格类型 ''/card/border-card
     * @return $this
     */
    public function tab($label, \Closure $closure, $position = 'top', $type = 'card')
    {
        array_push($this->formItem, ['type' => 'tabs', 'style' => $type, 'position' => $position, 'label' => $label, 'closure' => $closure]);
        return $this;
    }

    /**
     * 布局
     * @param \Closure $closure
     */
    public function layout(\Closure $closure)
    {
        array_push($this->formItem, ['type' => 'layout', 'closure' => $closure]);
        return $this;
    }

    /**
     * 更新数据
     * @param $id  主键id
     * @param $data 更新数据
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update($id, $data)
    {
        $res =$this->autoSave($data,$id);
        return $res;
    }
    /**
     * 数据保存
     */
    public function save($data)
    {
        $res = $this->autoSave($data);
        return $res;
    }
    protected function autoSave($data,$id=null){
        $res = false;
        $this->parseFormItem();
        $this->checkRule($data);
        //保存前回调
        if (!is_null($this->beforeSave)) {
            $beforePost = call_user_func($this->beforeSave, $data, $this->data);
            if (is_array($beforePost)) {
                $data = array_merge($data, $beforePost);
            }
        }
        Db::startTrans();
        try {
            $pk = $this->model->getPk();
            if(!is_null($id)){
                $this->model = $this->model->find($id);
            }
            $res =  $this->model->save($data);
            foreach ($data as $field=>$value){
                if(method_exists($this->model,$field)){
                    //多对多关联保存
                    if($this->model->$field() instanceof BelongsToMany){
                        $relationData = $value;
                        $this->model->$field()->detach();
                        if (is_string($relationData)) {
                            $relationData = explode(',', $relationData);
                            $relationData = array_filter($relationData);
                        }
                        if (count($relationData) > 0) {
                            $this->model->$field()->saveAll($relationData);
                        }
                    }
                }
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $res = false;
        }
        //保存回后调
        if (!is_null($this->afterSave)) {
            call_user_func_array($this->afterSave, [$data, $res]);
        }
        return $res;
    }
    /**
     * 保存后回调
     * @param \Closure $closure
     */
    public function saved(\Closure $closure)
    {
        $this->afterSave = $closure;
    }

    /**
     * 保存前回调
     * @param \Closure $closure
     */
    public function saving(\Closure $closure)
    {
        $this->beforeSave = $closure;
    }



    /**
     * 获取模型当前数据
     * @Author: rocky
     * 2019/8/22 14:56
     * @return array|mixed
     */
    public function getData($field = null)
    {
        if(is_null($field)){
            return $this->data;
        }else{
            if(method_exists($this->model,$field)){
                if($this->model->$field() instanceof BelongsToMany){
                    $pk = $this->model->$field()->getPk();
                    $relationData = $this->data->$field;
                    if (is_null($relationData)) {
                        $val = [];
                    } else {
                        $val = $relationData->column($pk);
                    }
                    return $val;
                }
            }else{
                if(isset($this->data[$field])){
                    return $this->data[$field];
                }
            }
        }
        return null;
    }

    /**
     * 数据编辑
     * @param $id 主键id
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
        $this->data = $this->model->find($id);
        $this->formData[$this->model->getPk()] = $id;
        $this->isEdit = true;
        return $this;
    }

    /**
     * 当前表单是否编辑模式
     * @return string 返回add或edit
     */
    public function isEdit()
    {
        return $this->isEdit;
    }

    /**
     * 添加表单元素
     * @param $class 组件类
     * @param $field 字段
     * @param $label 标签
     * @return mixed
     */
    protected function formItem($name, $field, $label)
    {
        $class = "thinkEasy\\form\\";

        if ($name == 'text' || $name == 'textarea' || $name == 'number' || $name == 'password') {
            $class .= 'Input';

        } else {
            $class .= ucfirst($name);
        }
        $formItem = new $class($field, $label);

        if ($name == 'number') {
            $formItem->setAttr('type', 'number');
        } elseif ($name == 'password') {
            $formItem->password();
        } elseif ($name == 'textarea') {
            $formItem->setAttr('type', 'textarea');
        }
        $this->formItem[] = $formItem;
        return $formItem;

    }

    /**
     * 解析formItem
     * @return string
     */
    protected function parseFormItem($formItemHtml = '')
    {
        foreach ($this->formItem as $key => $formItem) {
            if (is_array($formItem)) {
                $formItemArr = array_slice($this->formItem, $key + 1);
                $this->formItem = [];
                call_user_func_array($formItem['closure'], [$this]);
                switch ($formItem['type']) {
                    case 'layout':
                        $this->layout = true;
                        $formItemHtml = "<el-row>{$formItemHtml}</el-row>";
                        $formItemHtml = '<el-row>' . $this->parseFormItem($formItemHtml) . '</el-row>';
                        $this->layout = false;
                        break;
                    case 'tabs':
                        if (is_null($this->tabs)) {
                            $this->tabs = new Tabs();
                        }
                        if (!empty($formItem['style'])) {
                            $this->tabs->setAttr('type', 'card');
                        }
                        $this->tabs->setAttr('tab-position', $formItem['position']);
                        $this->tabs->push($formItem['label'], $this->parseFormItem());
                        $formItemHtml = $this->tabs->render();
                        $this->scriptArr = array_merge($this->scriptArr, $this->tabs->getScriptVar());
                        break;
                }
                $this->formItem = $formItemArr;

            } else {
                if ($formItem instanceof Tree) {
                    $this->setVar('styleHorizontal', $formItem->styleHorizontal());

                }
                $formItemTmp = "<el-form-item ref='{$formItem->field}' :error='validates.{$formItem->field}ErrorMsg' :show-message='validates.{$formItem->field}ErrorShow' label='{$formItem->label}' prop='{$formItem->field}' :rules='{$formItem->rule}'>%s<span>{$formItem->helpText}</span></el-form-item>";
                $this->formValidate["{$formItem->field}ErrorMsg"] = '';
                $this->formValidate["{$formItem->field}ErrorShow"] = false;
                //设置默认值
                if ($this->isEdit) {
                    $fieldValue = $this->getData($formItem->field);
                    if(is_null($fieldValue)){
                        $this->formData[$formItem->field] = $formItem->defaultValue;
                    }else{
                        $this->formData[$formItem->field] = $fieldValue ;
                    }
                } else {
                    $this->formData[$formItem->field] = $formItem->defaultValue;
                }
                //设置固定值
                if (!is_null($formItem->value)) {
                    $this->formData[$formItem->field] = $formItem->value;
                }
                //合并表单验证规则
                list($rule, $msg) = $formItem->paseRule($formItem->createRules);
                $this->setRules($rule, $msg, 1);
                list($rule, $msg) = $formItem->paseRule($formItem->updateRules);
                $this->setRules($rule, $msg, 2);
                $formItemTmp = sprintf($formItemTmp, $formItem->render());
                $this->scriptArr = array_merge($this->scriptArr, $formItem->getScriptVar());
                if (!empty($formItem->md)) {
                    $formItemHtml .= sprintf($formItem->md, $formItemTmp);
                } else {
                    $formItemHtml .= $formItemTmp;
                }

            }
        }
        return $formItemHtml;
    }

    /**
     * 设置表单验证规则
     * @Author: rocky
     * 2019/8/9 10:45
     * @param $rule 验证规则
     * @param $msg 验证提示
     * @param int $type 1新增，2更新
     */
    public function setRules($rule, $msg, $type)
    {
        switch ($type) {
            case 1:
                $this->createRules['rule'] = array_merge($this->createRules['rule'], $rule);
                $this->createRules['msg'] = array_merge($this->createRules['msg'], $msg);
                break;
            case 2:
                $this->updateRules['rule'] = array_merge($this->updateRules['rule'], $rule);
                $this->updateRules['msg'] = array_merge($this->updateRules['msg'], $msg);
                break;
        }
    }

    /**
     * 验证表单规则
     * @param $data
     */
    public function checkRule($data)
    {
        if ($this->isEdit) {
            //更新
            $validate = Validate::rule($this->updateRules['rule'])->message($this->updateRules['msg']);
        } else {
            //新增
            $validate = Validate::rule($this->createRules['rule'])->message($this->createRules['msg']);
        }
        $result = $validate->batch(true)->check($data);
        if (!$result) {
            throw new HttpResponseException(json(['code' => 422, 'message' => '表单验证失败', 'data' => $validate->getError()]));
        }
    }

    /**
     * 设置标题
     * @param $title
     */
    public function setTitle($title)
    {
        $this->setVar('title', $title);
    }

    /**
     * 设置提交按钮文字
     * @return string
     */
    public function setSubmitText($text)
    {
        $this->setVar('submitText', $text);
    }

    /**
     * 隐藏重置按钮
     */
    public function hideResetButton()
    {
        $this->setVar('hideResetButton', true);
    }

    /**
     * 添加表单附加参数
     * @param array $data
     */
    public function addExtraData(array $data)
    {
        $this->extraData = array_merge($this->extraData, $data);
        return $this;
    }

    public function view()
    {
        $formItem = $this->parseFormItem();
        $scriptStr = implode(',', array_unique($this->scriptArr));
        list($attrStr, $formScriptVar) = $this->parseAttr();
        if (!empty($scriptStr)) {
            $formScriptVar = $scriptStr . ',' . $formScriptVar;
        }
        $this->formData = array_merge($this->formData, $this->extraData);
        $this->setVar('formData', json_encode($this->formData, JSON_UNESCAPED_UNICODE));
        $this->setVar('formValidate', json_encode($this->formValidate, JSON_UNESCAPED_UNICODE));
        $this->setVar('attrStr', $attrStr);
        $this->setVar('formItem', $formItem);
        $this->setVar('formScriptVar', $formScriptVar);
        if (Request::has('build_dialog')) {
            $this->setVar('title', '');
        }
        return $this->render();
    }
}
