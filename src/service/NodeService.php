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
    public function all(){
        $files = $this->getControllerFiles();
        return $this->parse($files);

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
        if(count($commentsLine) > 1){
            list($title,$auth) = $commentsLine;
            $auth = trim($auth) == '@auth' ? true : false;
        }else{
           return false;
        }
        return [trim($title),$auth];

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
                if($method->isProtected()){
                    if($method->name == 'grid'){
                        $res = $this->parseDocComment($doc);
                        if($res === false){
                            $data[$moduleName.'/'.$controller] = [
                                'title'=>$node,
                                'is_auth'=>false,
                            ];
                        }else{

                            list($title,$auth) = $res;
                            $data[$moduleName.'/'.$controller] = [
                                'title'=>$title,
                                'is_auth'=>$auth,
                            ];
                        }
                        continue;
                    }elseif ($method->name  == 'form'){
                        //TODO
                    }
                }
                if($method->class == $namespace){
                    $node  = $controller.'/'.$method->getName();
                    $res = $this->parseDocComment($doc);
                    if($res === false){
                        $data[$node] = [
                            'title'=>$node,
                            'is_auth'=>false,
                        ];
                    }else{
                        list($title,$auth) = $res;
                        $data[$node] = [
                            'title'=>$title,
                            'is_auth'=>$auth,
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