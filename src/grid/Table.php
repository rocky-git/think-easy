<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-12
 * Time: 16:11
 */

namespace thinkEasy\grid;


use thinkEasy\form\Dialog;
use think\helper\Str;
use thinkEasy\form\Drawer;
use thinkEasy\View;

class Table extends View
{
    protected $attrs = [
        'data',
        'stripe',
        'border',
        'fit',
        'show-header',
        'highlight-current-row',
        'current-row-key',
        'row-class-name',
        'row-style',
        'cell-class-name',
        'cell-style',
        'header-row-class-name',
        'header-row-style',
        'header-cell-class-name',
        'header-cell-style',
        'default-expand-all',
        'expand-row-keys',
        'default-sort',
        'show-summary',
        'span-method',
        'select-on-indeterminate',
        'lazy',
        'load',
        'tree-props',
    ];
    protected $scriptVarStr = '';
    protected $headers;
    protected $data;
    protected $cellComponent;
    protected $scriptArr = [];

    public function __construct($headers, array $data)
    {
        $this->template = 'table';
        $this->headers = $headers;
        $this->setAttr('data', $data);
        $this->setAttr('@sort-change', 'sortHandel');
        $this->setAttr('ref', 'dragTable');
        $this->setAttr('v-loading', 'loading');
    }

    //获取自定义内容组件
    public function cellComponent()
    {
        return $this->cellComponent;
    }

    /**
     * 对话框表单
     * @param $title 标题
     * @param bool $fullscreen 是否全屏
     * @param string $width 宽度
     */
    public function setFormDialog($title, $fullscreen = false, $width = "40%")
    {
        $dialog = new Dialog($title, "<component :is='plugDialog' :dialogVisible.sync='dialogVisible' :tableDataUpdate.sync='tableDataUpdate'></component>");
        $dialog->setAttr('width', $width);
        if ($fullscreen) {
            $dialog->setAttr('fullscreen', true);
        }
        $this->setVar('dialog', $dialog->render());
        $this->setVar('dialogVar', $dialog->getVisibleVar());
        $this->setVar('dialogTitleVar', $dialog->getTitleVar());
        $this->scriptArr = array_merge($this->scriptArr, $dialog->getScriptVar());
    }
    /**
     * 抽屉表单
     * @param $title 标题
     * @param bool $direction 打开的方向 rtl / ltr / ttb / btt
     * @param string $size 窗体的大小
     */
    public function setFormDrawer($title, $direction = 'rtl',$size = '30%')
    {
        $drawer = new Drawer($title, "<component :is='plugDialog' :dialogVisible.sync='dialogVisible' :tableDataUpdate.sync='tableDataUpdate'></component>");
        $drawer->setAttr('size', $size);
        $drawer->setAttr('direction', $direction);
        $drawer->setAttr('wrapper-closable', false);
        $this->setVar('dialog', $drawer->render());
        $this->setVar('dialogVar', $drawer->getVisibleVar());
        $this->setVar('dialogTitleVar', $drawer->getTitleVar());
        $this->scriptArr = array_merge($this->scriptArr, $drawer->getScriptVar());
    }
    /**
     * 设置列
     * @param $cloumns
     */
    public function setColumn($cloumns)
    {
        $this->headers = $cloumns;
    }

    public function setScriptArr($scriptArr)
    {
        $this->scriptArr = array_merge($this->scriptArr, $scriptArr);
    }

    /**
     * 返回视图
     * @return string
     */
    public function view()
    {
        $columnHtml = '';
        $i = 0;
        foreach ($this->headers as $field => $label) {
            if ($label instanceof Column) {
                $column = $label;
            } else {
                $column = new Column($field, $label);
            }
            $this->cellComponent[] = $column->getDisplay($i, 'tableData');
            $i++;
            $columnHtml .= $column->render();
            $this->scriptArr = array_merge($this->scriptArr, $column->getScriptVar());
        }

        $columnScriptVar = implode(',', $this->scriptArr);
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        if (!empty($columnScriptVar)) {
            $tableScriptVar = $tableScriptVar . ',' . $columnScriptVar;
        }

        $tableHtml = '<el-table @selection-change="handleSelect" ' . $attrStr . '>' . $columnHtml . '</el-table>';
        $this->setVar('cellComponent', json_encode($this->cellComponent, JSON_UNESCAPED_UNICODE));
        $this->setVar('tableHtml', $tableHtml);
        $this->setVar('tableDataScriptVar', 'tableData' . $this->varMatk);
        $this->setVar('tableScriptVar', $tableScriptVar);
        return $this->render();
    }

}
