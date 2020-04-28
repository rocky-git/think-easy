<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-23
 * Time: 22:44
 */

namespace thinkEasy\service;

use thinkEasy\Service;

/**
 * 系统节点服务
 * Class NodeService
 * @package thinkEasy\service
 */
class NodeService extends Service
{
    //节点缓存key
    protected $cacheKey = 'eadmin_node_list';
    
    public function all(){
        if($this->app->cache->has($this->cacheKey)){
            return unserialize($this->app->cache->get($this->cacheKey)));
        }else{
            $files = $this->getControllerFiles();
            $data =  $this->parse($files);
            $this->app->cache->set($this->cacheKey),serialize($data));
            return $data;
        }
    }
    /**
     * 解析注释
     * @param $doc 注释
     * @return array|bool
     */
    protected function parseDocComment($doc){
        if (preg_match ( '#^/\*\*(.*)\*/#s', $doc, $comment ) === false){
            return false;
        }
        if(!isset($comment[1])){
            return false;
        }
        $comment = trim ( $comment [1] );
        if (preg_match_all ( '#^\s*\*(.*)#m', $comment, $lines ) === false){
            return false;
        }
        $commentsLine = end($lines);

        if(count($commentsLine) > 0){
            $auth = false;
            $title = array_shift($commentsLine);
            $method = 'any';
            foreach ($commentsLine as $line){
                $line = trim($line);
                if(preg_match('/@auth\s*true/i',$line) && $auth == false){
                    $auth  = true;
                }elseif (preg_match('/@method\s(.*)/i',$line,$methods) && $method == 'any'){
                    $method = $methods[1];
                }

            }
        }else{
           return false;
        }
        return [trim($title),$auth,$method];

    }
    /**
     * 解析控制器文件返回权限节点
     * @param $files
     * @throws \ReflectionException
     */
    protected function parse($files){
        $data = [];

        foreach ($files as $file){
            $controller = str_replace('.php','',basename($file));

            $path = dirname(dirname($file));
            $moduleName = basename($path);
            $appName = basename(dirname($path));
            $namespace = "$appName\\$moduleName\\controller\\$controller";
            $class = new \ReflectionClass($namespace);
            foreach ($class->getMethods() as $method){
                $doc = $method->getDocComment();
                $res = $this->parseDocComment($doc);
                if($method->isProtected()){
                    if($method->name == 'grid'){
                        $node = $moduleName.'/'.$controller;
                        $node = strtolower($node);
                        if($res === false){
                            $auth = false;
                            $data[] = [
                                'title'=>$node,
                                'rule'=>$node,
                                'is_auth'=>$auth,
                                'method'=>'get',
                            ];
                        }else{
                            list($title,$auth) = $res;
                            $data[] = [
                                'title'=>$title,
                                'rule'=>$node,
                                'is_auth'=>$auth,
                                'method'=>'get',
                            ];
                        }
                        continue;
                    }elseif ($method->name  == 'form'){
                        if($res === false){
                            $title = '';

                        }else{
                            list($title,$auth) = $res;
                        }
                        $data[] = [
                            'title'=>$title.'添加',
                            'rule'=>$node.'.rest',
                            'is_auth'=>$auth,
                            'method'=>'post',
                        ];
                        $data[] = [
                            'title'=>$title.'添加页面',
                            'rule'=>$node.'/create.rest',
                            'is_auth'=>$auth,
                            'method'=>'get',
                        ];
                        $data[] = [
                            'title'=>$title.'修改',
                            'rule'=>$node.'/:id.rest',
                            'is_auth'=>$auth,
                            'method'=>'put',
                        ];
                        $data[] = [
                            'title'=>$title.'修改页面',
                            'rule'=>$node.'/:id/edit.rest',
                            'is_auth'=>$auth,
                            'method'=>'get',
                        ];
                        $data[] = [
                            'title'=>'删除权限',
                            'rule'=>$node.'/:id.rest',
                            'is_auth'=>$auth,
                            'method'=>'delete',
                        ];
                        continue;
                    }elseif ($method->name == 'detail'){
                        if($res === false){
                            $title = '详情';
                            $auth = false;
                        }else{
                            list($title,$auth) = $res;
                        }
                        $data[] = [
                            'title'=>$title,
                            'rule'=>$node.'/:id.rest',
                            'is_auth'=>$auth,
                            'method'=>'get',
                        ];
                        continue;
                    }
                }
                if($method->class == $namespace){
                    $node  = $moduleName.'/'.$controller.'/'.$method->getName();
                    $node = strtolower($node);
                    if($res === false){
                        $data[] = [
                            'title'=>$node,
                            'rule'=>$node,
                            'is_auth'=>false,
                            'method'=>'any',
                        ];
                    }else{
                        list($title,$auth,$method) = $res;
                        $data[] = [
                            'title'=>$title,
                            'rule'=>$node,
                            'is_auth'=>$auth,
                            'method'=>$method,
                        ];
                    }
                }
            }
        }
        return $data;
    }
    /**
     * 获取所有模块控制器文件
     * @return array
     */
    protected function getControllerFiles(){
        $appPath = $this->app->getBasePath();
        //扫描所有模块
        $modules = [];
        foreach (glob($appPath.'*') as $file){
            if(is_dir($file)){
                $modules[] = $file;
            }
        }
        //扫描模块控制器下所有文件
        foreach ($modules as $module){
            foreach (glob($module.'/controller/'.'*.php') as $file){
                if(is_file($file)){
                    $controllerFiles[] =  $file;
                }
            }
        }
        return $controllerFiles;
    }
}