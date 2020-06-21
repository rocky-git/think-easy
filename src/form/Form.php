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
use think\helper\Str;
use think\Model;
use think\model\relation\BelongsTo;
use think\model\relation\BelongsToMany;
use think\model\relation\HasMany;
use think\model\relation\HasOne;
use thinkEasy\model\SystemConfig;
use thinkEasy\View;

/**
 * Class Form
 * @package thinkEasy\form
 * @method \thinkEasy\form\Input text($field, $label) 文本输入框
 * @method \thinkEasy\form\Input hidden($field) 隐藏输入框
 * @method \thinkEasy\form\Input textarea($field, $label) 多行文本输入框
 * @method \thinkEasy\form\Input password($field, $label) 密码输入框
 * @method \thinkEasy\form\Input number($field, $label) 数字输入框
 * @method \thinkEasy\form\Select select($field, $label) 下拉选择器
 * @method \thinkEasy\form\Radio radio($field, $label) 单选框
 * @method \thinkEasy\form\Switchs switch ($field, $label) switch开关
 * @method \thinkEasy\form\Tree tree($field, $label) 树形
 * @method \thinkEasy\form\DateTime datetime($field, $label) 日期时间
 * @method \thinkEasy\form\DateTime datetimeRange($startFiled, $endField, $label) 日期时间范围时间
 * @method \thinkEasy\form\DateTime dateRange($startFiled, $endField, $label) 日期范围时间
 * @method \thinkEasy\form\DateTime timeRange($startFiled, $endField, $label) 日期范围时间
 * @method \thinkEasy\form\DateTime date($field, $label) 日期
 * @method \thinkEasy\form\DateTime dates($field, $label) 多选日期
 * @method \thinkEasy\form\DateTime time($field, $label) 时间
 * @method \thinkEasy\form\DateTime year($field, $label) 年
 * @method \thinkEasy\form\DateTime month($field, $label) 月
 * @method \thinkEasy\form\Checkbox checkbox($field, $label) 多选框
 * @method \thinkEasy\form\File file($field, $label) 文件上传
 * @method \thinkEasy\form\File image($field, $label) 图片上传
 * @method \thinkEasy\form\Editor editor($field, $label) 富文本编辑器
 * @method \thinkEasy\form\Slider slider($field, $label) 滑块
 * @method \thinkEasy\form\Color color($field, $label) 颜色选择器
 * @method \thinkEasy\form\Rate rate($field, $label) 评分组件
 * @method \thinkEasy\form\Cascader cascader(...$field, $label) 级联选择器
 * @method \thinkEasy\form\Transfer transfer($field, $label) 穿梭框
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
        'label-position',
        'validate-on-rule-change',
        'disabled',
        'unlink-panels',
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

    //表单item标记集合
    protected $formItemTags = [];

    //radio事件js
    protected $radioJs = null;
    //表字段
    protected $tableFields = [];

    protected $saveData = [];

    protected $hasManyRelation = null;

    protected $hasManyRowData = [];

    protected $hasManyIndex = 0;

    protected $pkField = 'id';

    public function __construct($model = null)
    {
        if ($model instanceof Model) {
            $this->model = $model;
            $this->tableFields = $this->model->getTableFields();
            $this->pkField = $this->model->getPk();
        }
        $this->template = 'form';
        $this->labelPosition('right');
        $this->addExtraData([
            'submitFromMethod' => request()->action(),
        ]);
        if (request()->has($this->pkField)) {
            $this->edit(request()->param($this->pkField));
        }
    }

    /**
     * 设置主键字段
     * @param $field
     */
    public function setPkField($field)
    {
        $this->pkField = $field;
    }

    /**
     * 一对多
     * @param $label 标签
     * @param $relationMethod 关联方法
     * @param \Closure $closure
     */
    public function hasMany($label, $relationMethod, \Closure $closure)
    {
        if (method_exists($this->model, $relationMethod)) {
            if ($this->model->$relationMethod() instanceof HasMany) {
                array_push($this->formItem, ['type' => 'hasMany', 'label' => $label, 'relationMethod' => $relationMethod, 'closure' => $closure]);
            } else {
                abort(500, '关联方法不是一对多');
            }
        } else {
            abort(500, '无效关联方法');
        }
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
     * 对齐方式
     * @param $position top,left,right
     * @param int $width 宽度
     */
    public function labelPosition($position, $width = 120)
    {
        $this->setAttr('label-width', $width . 'px');
        $this->setAttr('label-position', $position);
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
        $res = $this->autoSave($data, $id);
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

    protected function autoSave($data, $id = null)
    {
        $res = false;
        $this->saveData = $data;
        $this->parseFormItem();
        $this->checkRule($this->saveData);
        Db::startTrans();
        try {
            //保存前回调
            if (!is_null($this->beforeSave)) {
                $beforePost = call_user_func($this->beforeSave, $this->saveData, $this->data);
                if (is_array($beforePost)) {
                    $this->saveData = array_merge($this->saveData, $beforePost);
                }
            }
            if (is_null($this->model)) {
                foreach ($this->saveData as $name => $value) {
                    if ($name == 'empty' || $name == 'submitFromMethod') {
                        continue;
                    }
                    $sysconfig = SystemConfig::where('name', $name)->find();
                    if ($sysconfig) {
                        $sysconfig->value = $value;
                        $res = $sysconfig->save();
                    } else {
                        $res = SystemConfig::create([
                            'name' => $name,
                            'value' => $value,
                        ]);
                    }
                }
            } else {
                if (!is_null($id)) {
                    $this->data = $this->model->where($this->pkField, $id)->find();
                    $this->model = $this->model->where($this->pkField, $id)->find();
                } elseif (isset($this->saveData[$this->pkField])) {
                    $isExists = Db::name($this->model->getTable())->where($this->pkField, $this->saveData[$this->pkField])->find();
                    if ($isExists) {
                        $this->data = $this->model->where($this->pkField, $this->saveData[$this->pkField])->find();
                        $this->model = $this->model->where($this->pkField, $this->saveData[$this->pkField])->find();
                    }
                }
                $res = $this->model->save($this->saveData);
                foreach ($this->saveData as $field => $value) {
                    if (method_exists($this->model, $field)) {
                        //多对多关联保存
                        if ($this->model->$field() instanceof BelongsToMany) {
                            $relationData = $value;
                            $this->model->$field()->detach();
                            if (is_string($relationData)) {
                                $relationData = explode(',', $relationData);
                                $relationData = array_filter($relationData);
                            }
                            if (count($relationData) > 0) {
                                $this->model->$field()->saveAll($relationData);
                            }
                        } elseif ($this->model->$field() instanceof HasOne || $this->model->$field() instanceof BelongsTo) {
                            $relationData = $this->saveData[$field];
                            if (is_null($id) || empty($this->data->$field)) {
                                $this->model->$field()->save($relationData);
                            } else {
                                $this->data->$field->save($relationData);
                            }

                        } elseif ($this->model->$field() instanceof HasMany) {
                            $realtionUpdateIds = array_column($value, 'id');
                            if(!empty($this->data->$field)){
                                $deleteIds = $this->data->$field->column('id');

                                if (is_array($realtionUpdateIds)) {
                                    $deleteIds = array_diff($deleteIds, $realtionUpdateIds);

                                }
                                if (count($deleteIds) > 0) {
                                    $res = $this->model->$field()->whereIn($this->pkField, $deleteIds)->delete();
                                }
                            }
                            $this->model->$field()->saveAll($value);
                        }
                    }
                }
            }
            //保存回后调
            if (!is_null($this->afterSave)) {
                call_user_func_array($this->afterSave, [$this->saveData, $res]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $res = false;
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
     * 数据编辑
     * @param $id 主键id
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
        $this->data = $this->model->where($this->pkField, $id)->find();
        $this->formData[$this->pkField] = $id;
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
    protected function formItem($name, $field, $arguments)
    {
        $label = array_pop($arguments);
        $class = "thinkEasy\\form\\";
        $inputs = [
            'text',
            'textarea',
            'number',
            'password',
            'hidden',
        ];
        $dates = [
            'date',
            'dates',
            'time',
            'year',
            'month',
            'datetime',
            'datetimeRange',
            'dateRange',
            'timeRange',
        ];
        if (in_array($name, $inputs)) {
            $class .= 'Input';
        } elseif (in_array($name, $dates)) {

            $class .= 'DateTime';
        } elseif ($name == 'switch') {
            $class .= 'Switchs';
        } elseif ($name == 'image') {
            $class .= 'File';
        } else {
            $class .= ucfirst($name);
        }
        $formItem = new $class($field, $label, $arguments);
        switch ($name) {
            case 'image':
                $formItem->displayType('image')->imageExt()->size(120, 120)->isUniqidmd5();
                break;
            case 'number':
                $formItem->setAttr('type', 'number');
                break;
            case 'password':
                $formItem->password();
                break;
            case 'hidden':
                $formItem->hidden();
                break;
            case 'textarea':
                $formItem->setAttr('type', 'textarea');
                break;
            case 'dateTime':
                $formItem->setType('datetime');
            case 'datetimeRange':
                $formItem->setType('datetime')->range();;
                break;
            case 'dateRange':
                $formItem->setType('date')->range();;
                break;
            case 'timeRange':
                $formItem->setType('time')->range();;
                break;
            case 'time':
                $formItem->setType('time');
                break;
            case 'year':
                $formItem->setType('year');
                break;
            case 'month':
                $formItem->setType('month');
                break;
            case 'dates':
                $formItem->setType('dates');
                break;
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
                    case 'hasMany':
                        $this->hasManyRelation = $formItem['relationMethod'];
                        $manyData = $this->getData($this->hasManyRelation);
                        $formItemHtml .= "<div v-for='(manyItem,manyIndex) in form.{$this->hasManyRelation}' :key='manyIndex'>";
                        $formItemHtml .= "<el-divider content-position='left'>{$formItem['label']}</el-divider>";
                        $formItemHtml = $this->parseFormItem($formItemHtml);
                        $encodeManyData = urlencode(json_encode($this->hasManyRowData, JSON_UNESCAPED_UNICODE));
                        $formItemHtml .= "<el-form-item><el-button type='primary' plain @click=\"addManyData('{$this->hasManyRelation}','{$encodeManyData}')\">新增</el-button><el-button type='danger' v-show='form.{$this->hasManyRelation}.length > 1' @click=\"removeManyData('{$this->hasManyRelation}',manyIndex)\">移除</el-button><el-button @click=\"handleUp('{$this->hasManyRelation}',manyIndex)\" v-show='form.{$this->hasManyRelation}.length > 1 && manyIndex > 0'>上移</el-button><el-button v-show='form.{$this->hasManyRelation}.length > 1 && manyIndex < form.{$this->hasManyRelation}.length-1' @click=\"handleDown('{$this->hasManyRelation}',manyIndex)\">下移</el-button></el-form-item>";
                        $formItemHtml .= "</div><el-divider></el-divider>";
                        if (is_null($manyData)) {
                            $this->formData[$this->hasManyRelation][] = $this->hasManyRowData;
                        } else {
                            $this->formData[$this->hasManyRelation] = $manyData;
                        }
                        $this->hasManyRelation = null;
                        break;
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
                if (is_null($this->hasManyRelation)) {
                    $valdateField = str_replace('.', '_', $formItem->field);
                    $this->formValidate["{$valdateField}ErrorMsg"] = '';
                    $this->formValidate["{$valdateField}ErrorShow"] = false;
                    $formItemTmp = "<el-form-item v-show=\"formItemTags.indexOf('{$formItem->getTag()}0') === -1\" ref='{$formItem->field}' :error='validates.{$valdateField}ErrorMsg' :show-message='validates.{$valdateField}ErrorShow' label='{$formItem->label}' prop='{$formItem->field}' :rules='formItemTags.indexOf(\"{$formItem->getTag()}0\") === -1 ? {$formItem->rule}:{required:false}'>%s<span style='font-size: 12px'>{$formItem->helpText}</span></el-form-item>";


                    //是否多个字段解析
                    if (count($formItem->fields) > 1) {
                        $fieldValue = [];
                        foreach ($formItem->fields as $field) {
                            $fieldValue[] = $this->getData($field);
                        }
                    } else {
                        $fieldValue = $this->getData($formItem->field);
                    }


                    //级联选择器一对多关系单独解析获取值
                    if ($formItem instanceof Cascader) {
                        $relation = $formItem->getRelation();
                        if ($relation) {
                            $fieldValue = [];
                            $manyDatas = $this->getData($relation);
                            if($manyDatas){
                                foreach ($manyDatas as $key=>$manyData){
                                    foreach ($formItem->fields as $field){
                                        if(!empty($manyData[$field])){
                                            $fieldValue[$key][] = $manyData[$field];
                                        }
                                    }
                                }
                            }
                            $formItem->setAttr('v-model','form.'.$relation);
                            $formItem->setField($relation);
                        }
                    }

                    //设置默认值
                    if ($this->isEdit) {
                        if (is_null($fieldValue)) {
                            $this->setData($formItem->field, $formItem->defaultValue);
                        } else {
                            $this->setData($formItem->field, $fieldValue);
                        }
                    } else {

                        if (is_array($fieldValue)) {
                            $this->setData($formItem->field, $fieldValue);
                        } else {
                            $this->setData($formItem->field, $formItem->defaultValue);
                        }
                    }
                    //设置固定值
                    if (!is_null($formItem->value)) {
                        $this->setData($formItem->field, $formItem->value);
                    }
                } else {
                    //一对多解析
                    $formItem->setAttr('@blur', "clearValidateArr(\"{$formItem->field}\",manyIndex)");
                    $formItem->setAttr('v-model', 'manyItem.' . $formItem->field);
                    $valdateField = str_replace('.', '_', $this->hasManyRelation . '.' . $formItem->field);
                    $this->formValidate["{$valdateField}ErrorMsg"] = '';
                    $this->formValidate["{$valdateField}ErrorShow"] = false;
                    $formItemTmp = "<el-form-item v-show=\"formItemTags.indexOf('{$formItem->getTag()}' + manyIndex) === -1\" ref='{$formItem->field}' :error='validates.{$valdateField}ErrorMsg' :show-message='validates.{$valdateField}ErrorShow' label='{$formItem->label}' :prop=\"'{$this->hasManyRelation}.' + manyIndex + '.{$formItem->field}'\" :rules='formItemTags.indexOf(\"{$formItem->getTag()}\" + manyIndex) === -1 ? {$formItem->rule}:{required:false}'>%s<span style='font-size: 12px'>{$formItem->helpText}</span></el-form-item>";
                    //一对多设置null，解析formItem初始值
                    $fieldValue = null;
                    //设置默认值
                    if ($this->isEdit) {
                        if (is_null($fieldValue)) {
                            $this->hasManyRowData[$formItem->field] = $formItem->defaultValue;
                        } else {
                            $this->hasManyRowData[$formItem->field] = $fieldValue;
                        }
                    } else {
                        if (is_array($fieldValue)) {
                            $this->hasManyRowData[$formItem->field] = $fieldValue;
                        } else {
                            $this->hasManyRowData[$formItem->field] = $formItem->defaultValue;
                        }
                    }
                    //设置固定值
                    if (!is_null($formItem->value)) {
                        $this->hasManyRowData[$formItem->field] = $formItem->value;
                    }
                    //一对多radio事件初始化值
                    if ($formItem instanceof Radio) {
                        $manyData = $this->getData($this->hasManyRelation);
                        $radioJs = <<<EOF
                        this.form.{$this->hasManyRelation}.forEach((item,index)=>{
                            this.radioChange(item.{$formItem->field},'{$formItem->getTag()}',index)
                        })
EOF;
                        $this->script($radioJs);
                    }
                    $formItem->setField("{$this->hasManyRelation}.$formItem->field");
                }


                //合并表单验证规则
                list($rule, $msg) = $formItem->paseRule($formItem->createRules);
                $this->setRules($rule, $msg, 1);
                list($rule, $msg) = $formItem->paseRule($formItem->updateRules);
                $this->setRules($rule, $msg, 2);
                $render = $formItem->render();
                
                
                if (isset($this->saveData[$formItem->field]) && is_array($this->saveData[$formItem->field])) {
                    $field = $formItem->field;
                    $itemSaveValues = $this->saveData[$field];
                    $itemFields = $formItem->getFileds();
                    if($this->model->$field() instanceof HasMany){
                        //针对级联选择器多选解析保存一对多数据
                        $this->saveData[$field] = [];
                        foreach ($itemSaveValues as $index=>$itemSaveValue){
                            $saveHanyData = [];
                            foreach ($itemSaveValue as $key=>$value){
                                if(isset($itemFields[$key])){
                                    $saveHanyData[$itemFields[$key]] = $value;
                                }
                            }
                            $this->saveData[$field][] = $saveHanyData;
                        }
                    }else{
                        if (count($itemFields) > 1) {
                            foreach ($itemFields as $key => $itemField) {
                                if (isset($itemSaveValues[$key])) {
                                    $this->saveData[$itemField] = $itemSaveValues[$key];
                                }
                            }
                        }
                    }
                }
                
                
                if ($formItem instanceof Input && $formItem->isHidden()) {
                    $formItemTmp = $render;
                } else {
                    $formItemTmp = sprintf($formItemTmp, $render);
                }
                $this->scriptArr = array_merge($this->scriptArr, $formItem->getScriptVar());
                if (!empty($formItem->md)) {
                    $formItemHtml .= sprintf($formItem->md, $formItemTmp);
                } else {
                    $formItemHtml .= $formItemTmp;
                }
                $this->script($formItem->getScript());


                //when显示元素解析，item互动事件显示隐藏
                $whenTags = [];
                $whenTagsAll = [];
                foreach ($formItem->getWhenItem() as $whenIndex => $whenItem) {
                    $formItemArr = array_slice($this->formItem, $key + 1);
                    $this->formItem = [];
                    call_user_func_array($whenItem['closure'], [$this]);
                    $formItemHtml = $this->parseFormItem($formItemHtml);
                    foreach ($this->formItem as $whenformItem) {
                        $whenTags[$whenItem['value']][] = $whenformItem->getTag();
                        $whenTagsAll[] = $whenformItem->getTag();
                        $this->radioJs .= "if(val == '{$whenItem['value']}' && tag === '{$formItem->getTag()}'){this.deleteArr(this.formItemTags,'{$whenformItem->getTag()}' + manyIndex)}" . PHP_EOL;
                    }
                }
                foreach ($whenTags as $whenVal => $tags) {
                    $hideTags = array_diff($whenTagsAll, $tags);
                    $hideTags = array_map(function ($v) {
                        return "'{$v}' + manyIndex";
                    }, $hideTags);
                    $hideTags = implode(',', $hideTags);
                    $this->radioJs .= "if(val == '{$whenVal}' && tag === '{$formItem->getTag()}'){this.formItemTags.splice(-1,0,{$hideTags})}" . PHP_EOL;
                }
            }
        }
        return $formItemHtml;
    }

    /**
     * 设置js
     * @param $js
     */
    public function script($js)
    {
        $this->script .= $js . PHP_EOL;
    }

    protected function setData($field, $val)
    {
        if ($this->model instanceof Model) {
            if (strpos($field, '.')) {
                list($relation, $field) = explode('.', $field);
                $this->formData[$relation][$field] = $val;
            } else {

                $this->formData[$field] = $val;
            }
        } else {
            if (empty($val)) {
                $this->formData[$field] = SystemConfig::where('name', $field)->value('value');
            } else {
                $this->formData[$field] = $val;
            }
        }
    }

    /**
     * 获取模型当前数据
     * @Author: rocky
     * 2019/8/22 14:56
     * @return array|mixed
     */
    public function getData($field = null, $data = null)
    {
        if (is_null($data)) {
            $data = $this->data;
        }
        if (is_null($field)) {
            return $this->data;
        } else {
            if (method_exists($this->model, $field)) {
                if ($this->model->$field() instanceof BelongsToMany) {
                    if (empty($data->$field)) {
                        $relationData = null;
                    } else {
                        $relationData = $data->$field;
                    }
                    if (is_null($relationData)) {
                        $val = [];
                    } else {
                        $val = $relationData->column($this->pkField);
                    }
                    return $val;
                } elseif ($this->model->$field() instanceof HasMany) {
                    if (empty($data->$field)) {
                       return [];
                    } else {
                        return $data->$field;
                    }
                }
            }
            foreach (explode('.', $field) as $f) {
                if (isset($data[$f])) {
                    $data = $data[$f];
                } else {
                    $data = null;
                }
            }
            return $data;
        }
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
     * @param $datas
     */
    public function checkRule($datas)
    {
        if ($this->isEdit) {
            //更新
            $validate = Validate::rule($this->updateRules['rule'])->message($this->updateRules['msg']);
        } else {
            //新增
            $validate = Validate::rule($this->createRules['rule'])->message($this->createRules['msg']);
        }
        foreach ($datas as $field => $data) {
            if (method_exists($this->model, $field) && $this->model->$field() instanceof HasMany) {
                foreach ($data as $value) {
                    $valdateData[$field] = $value;
                    $result = $validate->batch(true)->check($valdateData);
                    if (!$result) {
                        throw new HttpResponseException(json(['code' => 422, 'message' => '表单验证失败', 'data' => $validate->getError()]));
                    }
                }

            }
        }
        $result = $validate->batch(true)->check($datas);
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
        if (isset($this->extraData[$this->pkField])) {
            $this->edit($this->extraData[$this->pkField]);
        }
        $formItem = $this->parseFormItem();
        $scriptStr = implode(',', array_unique($this->scriptArr));
        list($attrStr, $formScriptVar) = $this->parseAttr();
        if (!empty($scriptStr)) {
            $formScriptVar = $scriptStr . ',' . $formScriptVar;
        }
        $this->formData = array_merge($this->formData, $this->extraData);
        $this->setVar('formData', json_encode($this->formData, JSON_UNESCAPED_UNICODE));
        $this->setVar('formValidate', json_encode($this->formValidate, JSON_UNESCAPED_UNICODE));
        $this->setVar('formItemTags', json_encode($this->formItemTags, JSON_UNESCAPED_UNICODE));
        $this->setVar('script', $this->script);
        $this->setVar('attrStr', $attrStr);
        $this->setVar('radioJs', $this->radioJs);
        $this->setVar('formItem', $formItem);
        $submitUrl = app('http')->getName() . '/' . request()->controller();
        $submitUrl = str_replace('.rest', '', $submitUrl);
        $this->setVar('submitUrl', $submitUrl);
        $this->setVar('formScriptVar', $formScriptVar);
        if (Request::has('build_dialog')) {
            $this->setVar('title', '');
        }
        return $this->render();
    }

    public function __call($name, $arguments)
    {
        return $this->formItem($name, $arguments[0], array_slice($arguments, 1));
    }
}
