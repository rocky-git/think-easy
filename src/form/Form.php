<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-18
 * Time: 20:32
 */

namespace thinkEasy\form;


use think\facade\Request;
use think\Model;
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
    
    protected $data = ['empty' => 0];

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->template = 'form';
        $this->setAttr('label-width', '120px');
        $this->addExtraData([
            'submitFromMethod' => request()->action(),
        ]);
        if(request()->has('id')){
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
        if (!is_null($this->beforeSave)) {
            $beforePost = call_user_func($this->beforeSave, $data, $this->data);
            if (is_array($beforePost)) {
                $data = array_merge($data, $beforePost);
            }
        }
        $this->model = $this->model->find($id);

        $res = $this->model->save($data);
        if (!is_null($this->afterSave)) {
            call_user_func_array($this->afterSave, [$data, $this->model]);
        }

        return $res;
    }
    //保存后回调
    public function saved(\Closure $closure)
    {
        $this->afterSave = $closure;
    }

    //保存前回调
    public function saving(\Closure $closure)
    {
        $this->beforeSave = $closure;
    }
    /**
     * 数据保存
     */
    public function save($data)
    {
        if (!is_null($this->beforeSave)) {
            $beforePost = call_user_func($this->beforeSave, $data, $this->data);
            if (is_array($beforePost)) {
                $data = array_merge($data, $beforePost);
            }
        }
        $res = $this->model->save($data);
        if (!is_null($this->afterSave)) {
            call_user_func_array($this->afterSave, [$data, $res]);
        }
        return $res;
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
        $this->data = $this->model->find($id)->toArray();
        return $this;
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
                if (empty($formItem->label)) {
                    $formItemTmp = "<div>%s</div>";
                } else {
                    $formItemTmp = "<el-form-item label='{$formItem->label}' prop='{$formItem->field}' :rules='{$formItem->rule}'>%s</el-form-item>";
                }
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

        $this->data = array_unique(array_merge($this->data, $this->extraData));
        $this->setVar('formData', json_encode($this->data, JSON_UNESCAPED_UNICODE));
        $this->setVar('attrStr', $attrStr);
        $this->setVar('formItem', $formItem);
        $this->setVar('formScriptVar', $formScriptVar);
        if (Request::has('build_dialog')) {
            $this->setVar('title', '');
        }
        return $this->render();
    }
}
