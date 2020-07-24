<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-14
 * Time: 21:31
 */

namespace thinkEasy\grid;


use thinkEasy\form\Switchs;
use thinkEasy\View;

class Column extends View
{
    protected $attrs = [
        'index',
        'render-header',
        'sort-method',
        'sort-by',
        'sort-orders',
        'formatter',
        'selectable',
        'reserve-selection',
        'filters',
        'filter-method',
        'filtered-value',
        'show-overflow-tooltip',
    ];
    protected $scopeTemplate = '<template slot-scope="scope">%s</template>';
    //自定义内容
    protected $display = '';

    //字段
    protected $field = '';

    public $label = '';

    //组件行字段
    protected $rowField = '';

    //内容颜射
    protected $usings = [];
    //映射标签颜色
    protected $tagColor = [];
    //映射标签颜色主题
    protected $tagTheme = 'light';
    //标签
    protected $tag = '';
    //自定义闭包
    protected $displayClosure = null;

    protected $cellVue;
    //占位栅格数
    protected $md = 24;
    //开启合计行
    protected $isTotalRow = false;
    //导出自定义显示闭包
    protected $exportClosure = null;
    //导出数据值
    protected $exportValue = '';

    public $totalText = '';
    public $closeExport = false;

    public function __construct($field = '', $label = '')
    {

        $this->label = $label;
        if (!empty($field)) {
            $this->field = $field;
            $field = $this->getField($field);
            $this->rowField = "scope.row.{$field}";
            $this->setAttr('prop', $field);
        }
        if (!empty($label)) {
            $this->setAttr('label', $label);
        }
    }

    public function getField($field = '')
    {
        if (empty($field)) {
            $field = $this->field;
        }
        $fields = explode('.', $field);
        return end($fields);
    }

    /**
     * 设置当内容过长被隐藏时显示
     * @return $this
     */
    public function tip()
    {
        $this->setAttr('show-overflow-tooltip', true);
        return $this;
    }

    /**
     * 评分显示
     */
    public function rate($max = 5){
        $this->display(function ($val) use($max){
            return "<el-rate v-model='data.{$this->field}' disabled :max='{$max}'></el-rate>";
        });
        return $this;
    }
    /**
     * 列是否固定
     * @param string $fixed
     */
    public function fixed($fixed = 'left')
    {
        $this->setAttr('fixed', $fixed);
        return $this;
    }

    /**
     * 设置宽度
     * @param int $number
     */
    public function width(int $number)
    {
        $this->setAttr('width', $number);
        return $this;
    }

    /**
     * 对齐方式
     * @param $align 左对齐 left/ 居中 center/ 右对齐 right
     */
    public function align($align)
    {
        $this->setAttr('align', $align);
        return $this;
    }

    /**
     * 设置最小宽度
     * @param int $number
     */
    public function minWidth(int $number)
    {
        $this->setAttr('min-width', $number);
        return $this;
    }

    /**
     * 标签显示
     * @param $color 标签颜色：success，info，warning，danger
     * @param $theme 主题：dark，light，plain
     */
    public function tag($color = '', $theme = 'dark',$size='mini')
    {
        $this->tag = "<el-tag effect='{$theme}' type='{$color}' size='{$size}'>%s</el-tag>";
        return $this;
    }

    /**
     * 内容映射
     * @param array $usings 映射内容
     * @param array $tagColor 标签颜色
     * @param tagTheme 标签颜色主题：dark，light，plain
     */
    public function using(array $usings, array $tagColor = [], $tagTheme = 'light')
    {
        $this->tagColor = $tagColor;
        $this->tagTheme = $tagTheme;
        $this->usings = $usings;
        return $this;
    }

    /**
     * 自定义显示
     * @param \Closure $closure
     * @return $this
     */
    public function display(\Closure $closure)
    {
        $this->displayClosure = $closure;
        return $this;
    }

    /**
     * 显示html
     * @return $this
     */
    public function html(){
        $this->display(function ($val) {
            return $val;
        });
        return $this;
    }
    public function getClosure()
    {
        return $this->displayClosure;
    }

    /**
     * switch开关
     * @param array $active 开启状态 [1=>'开启']
     * @param array $inactive 关闭状态 [0=>'关闭]
     */
    public function switch(array $active = [], array $inactive = [])
    {
        $this->display(function ($val, $data) use ($active, $inactive) {
            $switch = new Switchs('switch', '');
            $switch->setAttr(':row-data', 'data');
            if (count($active) > 0 && count($inactive) > 0) {
                $switch->state($active, $inactive);
            }
            $switch->setAttr('field', $this->field);
            $switch->setAttr('v-model', 'data.' . $this->field);
            return $switch->render();
        });
        return $this;
    }

    /**
     * 开启合计行
     * @param string $text
     */
    public function total($text = '')
    {
        $this->isTotalRow = true;
        $this->totalText = $text;
    }

    public function isTotal()
    {
        return $this->isTotalRow;
    }

    /**
     * 显示语音
     * @return $this
     */
    public function audio()
    {
        $this->display(function ($val, $data) {
            if (empty($val)) {
                return '--';
            }
            if (is_array($val)) {
                $audios = implode(',', $val);
            } else {
                $audios = $val;
            }
            return "<eadmin-audio url='$audios'></eadmin-audio>";
        });
        return $this;
    }

    /**
     * 显示文件
     * @return $this
     */
    public function file()
    {
        $this->display(function ($val, $data) {
            if (empty($val)) {
                return '--';
            }
            if (is_array($val)) {
                $files = $val;
                $htmlArr = [];
                foreach ($files as $file) {
                    $htmlArr[] = "<eadmin-download-file style='margin: 5px' url='$file'></eadmin-download-file>";
                }
                return implode('', $htmlArr);
            } else {
                return "<eadmin-download-file style='margin: 5px' url='$val'></eadmin-download-file>";
            }

        });
        return $this;
    }

    /**
     * 显示视频
     * @param int $width 宽度
     * @param int $height 高度
     * @return $this
     */
    public function video($width = 0,$height = 0)
    {
        $this->display(function ($val, $data) use ($width, $height) {
            if (empty($val)) {
                return '--';
            }
            if (is_array($val)) {
                $videos = implode(',', $val);
            } else {
                $videos = $val;
            }
            if($width && $height){
                return "<eadmin-video style='width: {$width}px;height:{$height}px' url='$videos'></eadmin-video>";
            }else{
                return "<eadmin-video url='$videos'></eadmin-video>";
            }
        });
        return $this;
    }

    /**
     * 显示图片
     * @param int $width 宽度
     * @param int $height 高度
     * @param int $radius 圆角
     * @return $this
     */
    public function image($width = 80, $height = 80, $radius = 5)
    {
        $this->display(function ($val, $data) use ($width, $height, $radius) {
            if (empty($val)) {
                return '--';
            }
            if (is_string($val)) {
                $images = explode(',', $val);
            } elseif (is_array($val)) {
                $images = $val;
            }
            $html = '';
            foreach ($images as $image) {
                $html .= "<el-image style='width: {$width}px; height: {$height}px;border-radius: {$radius}%' src='{$image}' fit='fit'></el-image>&nbsp;";
            }
            return $html;
        });
        return $this;
    }

    /**
     * 设置数据
     * @param $data
     */
    public function setData($data)
    {
        $rowData = $data;
        $val = $this->getValue($data);
        if (count($this->usings) > 0) {
            $this->exportValue = isset($this->usings[$val]) ? $this->usings[$val] : '';
        } else {
            $this->exportValue = $val;
        }

        if (isset($rowData['id'])) {
            $id = $rowData['id'];
        } else {
            $id = 0;
        }
        if (is_null($this->displayClosure) && strpos($this->field, '.')) {
            $this->cellVue .= "<span v-if='data.id == {$id}'>{$val}</span>";
        }
        if (!is_null($this->displayClosure)) {
            if (empty($rowData)) {
                $res = '';
            } else {
                $clone = clone $this;
                $res = call_user_func_array($this->displayClosure, [$val, $rowData, $clone]);
                if ($res instanceof self) {
                    $res = call_user_func_array($clone->getClosure(), [$val, $rowData, $clone]);
                }
                
                $this->exportValue = $res;
            }
            $this->cellVue .= "<span v-if='data.id == {$id}'>{$res}</span>";
        }

        if (!is_null($this->exportClosure)) {
            $res = call_user_func_array($this->exportClosure, [$val, $rowData]);
            $this->exportValue = $res;
        }
    }

    /**
     * 关闭excel 导出
     * @Author: rocky
     * 2019/10/9 16:54
     */
    public function closeExport()
    {
        $this->closeExport = true;
        return $this;
    }

    /**
     * 获取导出数据值
     * @return string
     */
    public function getExportValue()
    {
        return $this->exportValue;
    }

    /**
     * 导出数据自定义
     * @param \Closure $closure
     * @return $this
     */
    public function export(\Closure $closure)
    {
        $this->exportClosure = $closure;
        return $this;
    }

    /**
     * 获取数据
     * @param $data 行数据
     * @param null $field 字段
     * @return |null
     */
    public function getValue($data, $field = null)
    {

        if (is_null($field)) {
            $dataField = $this->field;
        } else {
            $dataField = $field;
        }
        if (empty($dataField)) {
            return null;
        }

        foreach (explode('.', $dataField) as $f) {
            if (isset($data[$f])) {
                $data = $data[$f];
            } else {
                $data = null;
            }
        }
        return $data;
    }

    public function getDisplay($key, $tableDataScriptVar)
    {
        if (!empty($this->cellVue)) {
            $this->display = '<component :is="cellComponent[' . $key . ']" :data="scope.row" :index="scope.$index" :showEditId.sync="showEditId" :showDetailId.sync="showDetailId" :page="page" :size="size" :total="total" :tableData.sync="' . $tableDataScriptVar . '"></component>';
            $cell = new Cell();
            $cell->setVar('cell', $this->cellVue);
            list($attrStr, $scriptVar) = $cell->parseAttr();
            $cell->setVar('scriptVar', $scriptVar);
            $this->cellVue = $cell->render();
        }
        return $this->cellVue;
    }

    public function getDetailDisplay($key)
    {

        if (!empty($this->cellVue)) {
            $this->display = '<component :is="cellComponent[' . $key . ']" :data="data"></component>';
            $cell = new Cell();
            $cell->setVar('cell', $this->cellVue);
            list($attrStr, $scriptVar) = $cell->parseAttr();
            $cell->setVar('scriptVar', $scriptVar);
            $this->cellVue = $cell->render();
        }
        return $this->cellVue;
    }

    /**
     * 开启排序
     */
    public function sortable()
    {
        $this->setAttr('sortable', 'custom');
        return $this;
    }

    /**
     * 占位栅格数，24栏占满
     * @param $num 数量
     * @return $this
     */
    public function md($num = 3)
    {
        $this->md = $num;
        return $this;
    }

    public function detailRender()
    {
        $label = '';
        if(!empty($this->label)){
            $label = "<span style='font-size: 14px;line-height: 50px;'>{$this->label}:</span>&nbsp;";
        }
        $this->rowField = 'data.' . $this->field;
        if (!empty($this->tag)) {
            $this->display = sprintf($this->tag, "{{{$this->rowField}}}");
        } elseif (count($this->usings) > 0) {
            $html = '';
            foreach ($this->usings as $key => $value) {
                if (is_string($key)) {
                    $html .= "<span style='font-size: 14px;' v-if=\"{$this->rowField} == '{$key}'\">%s</span>";
                } else {
                    $html .= "<span style='font-size: 14px;' v-if='{$this->rowField} == {$key}'>%s</span>";
                }
                if (isset($this->tagColor[$key])) {
                    $this->tag($this->tagColor[$key], $this->tagTheme);
                    $value = sprintf($this->tag, $value);
                }
                $html = sprintf($html, $value);
            }
            $this->display = $html;
        } elseif (empty($this->display) && !empty($this->field)) {
            $this->display = "<span style='font-size: 14px;' v-if=\"{$this->rowField} === null || {$this->rowField} === ''\">--</span><span style='font-size: 14px;' v-else>{{{$this->rowField}}}</span>";
        }
        $this->display = "<el-col :span='{$this->md}' style='border-bottom-width: 1px;border-bottom-style: solid;border-bottom-color: #f0f0f0;'>" . $label . $this->display . "</el-col>";
        list($attrStr, $dataStr) = $this->parseAttr();
        return $this->display;
    }

    public function render()
    {
        if (empty($this->display)) {
            if (!empty($this->tag)) {
                $html = sprintf($this->tag, "{{{$this->rowField}}}");
                $this->display = sprintf($this->scopeTemplate, $html);
            }
            if (count($this->usings) > 0) {
                $html = '';
                foreach ($this->usings as $key => $value) {
                    if (is_string($key)) {
                        $html .= "<span v-if=\"{$this->rowField} == '{$key}'\">%s</span>";
                    } else {
                        $html .= "<span v-if='{$this->rowField} == {$key}'>%s</span>";
                    }
                    if (isset($this->tagColor[$key])) {
                        $this->tag($this->tagColor[$key], $this->tagTheme);
                        $value = sprintf($this->tag, $value);
                    }
                    $html = sprintf($html, $value);
                }
                $this->display = sprintf($this->scopeTemplate, $html);
            }
        } else {
            $this->display = sprintf($this->scopeTemplate, $this->display);
        }
        if (empty($this->display) && !empty($this->field)) {
            $this->display = sprintf($this->scopeTemplate, "<span v-if=\"{$this->rowField} === null || {$this->rowField} === ''\">--</span><span v-else>{{{$this->rowField}}}</span>");
        }
        list($attrStr, $dataStr) = $this->parseAttr();

        return "<el-table-column $attrStr>" . $this->display . "</el-table-column>";
    }
}
