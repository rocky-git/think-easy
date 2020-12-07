<?php

namespace app\admin\controller;


use think\facade\Request;
use think\facade\View;
use thinkEasy\controller\BaseAdmin;
use thinkEasy\form\Form;
use thinkEasy\grid\Actions;
use thinkEasy\grid\Filter;
use thinkEasy\grid\Grid;
use thinkEasy\model\SystemLog;
use thinkEasy\service\LogService;

/**
 * 系统日志
 * Class Log
 * @package app\admin\controller
 */
class Log extends BaseAdmin
{
    /**
     * 系统日志
     * @auth true
     * @login true
     * @return string
     */
    protected function grid()
    {
        $grid = new Grid(new SystemLog());
        $grid->setTitle('系统日志');
        $grid->indexColumn();
        $grid->column('action', '操作行为')->tag('','light');
        $grid->column('content', '操作内容');
        $grid->column('node', '操作节点');
        $grid->column('geoip', 'IP');
        $grid->column('username', '操作账号');
        $grid->column('create_time', '操作时间');
        $grid->filter(function (Filter $filter) {
            $filter->like('username', '操作账号');
            $filter->like('action', '操作行为');
            $filter->like('content', '操作内容');
            $filter->like('node', '操作节点');
            $filter->like('geoip', 'IP');
            $filter->dateRange('create_time', '操作时间');
        });
        $grid->hideAddButton();
        $grid->hideAction();
        return $grid;
    }
    /**
     * 日志数据
     * @return string
     */
    public function logData(){
        $offset = Request::param('offset',0);
        $file = Request::param('file',null);
        $limit = Request::param('limit',10);
        $log_time = Request::param('log_time');
        $content = Request::param('content');
        $log = LogService::instance($file);
        if(is_array($log_time)){
            $log_time = array_filter($log_time);
        }else{
            $log_time = [];
        }
        if(!empty($log_time) || !empty($content)){
            $list = $log->fetch(0,100000,4096,$content,$log_time);
        }else{
            $list = $log->fetch($offset,$limit);
        }
        $files = $log->getLogFiles();
        if(is_null($file)){
            $file = $log->getLastModifiedLog();
        }
        $this->successCode([
            'list'=>$list,
            'files'=>$files,
            'file'=>$file,
            'next'=>$log->getNextPageUrl($content,$log_time),
            'prev'=> $log->getPrevPageUrl($content,$log_time),
            'page'=> $log->page,
        ]);
    }
    /**
     * 日志查看
     * @return string
     */
    public function debug(){
        $view = View::fetch('/log');
        $this->successCode($view);
    }
    /**
     * 删除日志
     * @return string
     */
    public function remove(){
        $path = $this->request->post('path');
        $res = unlink($path);
        if($res){
            $this->successCode();
        }else{
            $this->errorCode();
        }
    }
}
