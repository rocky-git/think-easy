<?php

namespace thinkEasy\controller;

use think\facade\Request;
use think\facade\View;
use thinkEasy\service\LogService;

/**
 * 系统日志
 * Class Log
 * @package
 */
class Log extends BaseAdmin
{
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
     * 调试日志查看
     * @return string
     */
    public function debug(){
        $content = file_get_contents(__DIR__.'/../view/log.vue');
        $this->successCode($content);
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
