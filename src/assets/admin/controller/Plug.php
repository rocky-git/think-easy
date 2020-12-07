<?php


namespace app\admin\controller;

use thinkEasy\controller\BaseAdmin;
use thinkEasy\facade\Button;
use thinkEasy\grid\Column;
use thinkEasy\grid\Table;
use thinkEasy\service\PlugService;

/**
 * 插件管理
 * Class Plug
 * @package app\admin\controller
 */
class Plug extends BaseAdmin
{
    /**
     * 插件列表
     * @auth true
     * @login true
     */
    protected function grid()
    {
        $datas = PlugService::instance()->all();
        $columns[] = new Column('name', '插件信息');
        $columns[] = new Column('action', '操作');
        foreach ($datas as $key => &$rows) {
            $rows['id'] = $key;
            foreach ($columns as $column) {
                $field = $column->getField();
                if ($field == 'name') {
                    $column->display(function () use ($rows) {
                        $html = <<<EOF
<div style='display: flex;justify-content: space-between;align-items: center'>
    <div style="width: 100px;;height:100px;display: flex;justify-content: center;align-items: center">
        <el-avatar shape='square' :size="60" fit="fit" src='{$rows['logo']}'></el-avatar>
    </div>
    <div style="flex: 1">
        名称 : <b>{$rows['name']} &nbsp;<el-tag size="mini">{$rows['version']}</b></el-tag><br>描述 : {$rows['description']}<br>作者 : {$rows['author']}
    </div>
</div> 
EOF;
                        return $html;
                    });
                }
                if ($field == 'action') {
                    $column->display(function () use ($rows) {
                        if ($rows['is_install']) {
                            $button = Button::create('卸载', 'danger')->save($rows['class'], ['class' => $rows['class'], 'type' => 2], url('install'), '确认卸载？', true);
                        } else {
                            $button = Button::create('安装', 'primary')->save($rows['class'], ['class' => $rows['class'], 'type' => 1], url('install'), '确认安装？', true);
                        }
                        return $button;
                    });
                }
                $column->setData($rows);
            }
        }
        $table = new Table($columns, $datas);
        return $this->view($table);
    }
    /**
     * 安装/卸载
     * @auth true
     * @login true
     */
    public function install()
    {
        $type = $this->request->put('type');
        $class = $this->request->put('class');
        if ($type == 1) {
            PlugService::instance()->install($class);
        } else {
            PlugService::instance()->uninstall($class);
        }
        $this->successCode();
    }
}
