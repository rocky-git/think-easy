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

/**
 * Class Form
 * @package app\admin\buildView
 * @method \app\admin\buildView\form\Input text($field, $label) 文本输入框
 * @method \app\admin\buildView\form\Input textarea($field, $label) 多行文本输入框
 * @method \app\admin\buildView\form\Input password($field, $label) 密码输入框
 * @method \app\admin\buildView\form\Input number($field, $label) 数字输入框
 * @method \app\admin\buildView\form\Select select($field, $label) 下拉选择器
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

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->template = 'form';
        $this->setTitle('添加');
        $this->setAttr('label-width', '120px');
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
    public function tab($label,\Closure $closure,$position = 'top',$type='card'){
        array_push($this->formItem, ['type'=>'tabs','style'=>$type,'position'=>$position,'label'=>$label,'closure'=>$closure]);
        return $this;
    }
    /**
     * 布局
     * @param \Closure $closure
     */
    public function layout(\Closure $closure)
    {
        array_push($this->formItem, ['type'=>'layout','closure'=>$closure]);
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
        $class = "app\\admin\\buildView\\form\\";
        if ($name == 'text' || $name == 'textarea' || $name == 'number' || $name == 'password') {
            $class .= 'Input';

        } else {
            $class .= ucfirst($name);
        }
        $formItem = new $class($field, $label);
        if($name == 'number'){
            $formItem->setAttr('type','number');
        }elseif ($name == 'password'){
            $formItem->password();
        }elseif ($name == 'textarea'){
            $formItem->setAttr('type','textarea');
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
                switch ($formItem['type']){
                    case 'layout':
                        $this->layout = true;
                        $formItemHtml = "<el-row>{$formItemHtml}</el-row>";
                        $formItemHtml ='<el-row>'.$this->parseFormItem($formItemHtml).'</el-row>';
                        $this->layout = false;
                        break;
                    case 'tabs':
                        if(is_null($this->tabs)){
                            $this->tabs = new Tabs();
                        }
                        if(!empty($formItem['style'])){
                            $this->tabs->setAttr('type','card');
                        }
                        $this->tabs->setAttr('tab-position',$formItem['position']);
                        $this->tabs->push($formItem['label'],$this->parseFormItem());
                        $formItemHtml = $this->tabs->render();
                        $this->scriptArr = array_merge($this->scriptArr, $this->tabs->getScriptVar());
                        break;
                }
                $this->formItem = $formItemArr;

            } else {
                $formItemTmp = "<el-form-item label='{$formItem->label}' prop='{$formItem->field}' :rules='{$formItem->rule}'>%s</el-form-item>";
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

    public function view()
    {
        $formItem = $this->parseFormItem();
        $scriptStr = implode(',', array_unique($this->scriptArr));

        list($attrStr, $formScriptVar) = $this->parseAttr();
        if (!empty($scriptStr)) {
            $formScriptVar = $formScriptVar .$scriptStr;
        }
        $this->setVar('attrStr', $attrStr);
        $this->setVar('formItem', $formItem);
        $this->setVar('formScriptVar', $formScriptVar);
        if(Request::has('build_dialog')){
            $this->setVar('title', '');
        }
        return $this->render();
    }
}
