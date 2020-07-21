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
    protected $treeArr = [];
    public function all(){
        if($this->app->cache->has($this->cacheKey)){
            return unserialize($this->app->cache->get($this->cacheKey));
        }else{
            $files = $this->getControllerFiles();
            $data =  $this->parse($files);
            $this->app->cache->set($this->cacheKey,serialize($data));
            return $data;
        }
    }
    //树形格式
    public function tree(){
        $files = $this->getControllerFiles();
        $this->parse($files);
        $data = [];
        foreach ($this->treeArr as $tree){
            $data[] = $tree;
        }
        return $data;
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
            $login = false;
            $title = array_shift($commentsLine);
            $method = 'any';
            foreach ($commentsLine as $line){
                $line = trim($line);
                if(preg_match('/@auth\s*true/i',$line) && $auth == false){
                    $auth  = true;
                }elseif(preg_match('/@login\s*true/i',$line) && $login == false){
                    $login  = true;
                }elseif (preg_match('/@method\s(.*)/i',$line,$methods) && $method == 'any'){
                    $method = $methods[1];
                }

            }
        }else{
           return false;
        }
        return [trim($title),$auth,$login,$method];

    }
    /**
     * 解析控制器文件返回权限节点
     * @param $files
     * @throws \ReflectionException
     */
    protected function parse($files){

        $data = [];

        foreach ($files as $key=>$file){
            $controller = str_replace('.php','',basename($file));
            $path = dirname(dirname($file));
            $moduleName = basename($path);
            $appName = basename(dirname($path));
            $namespace = "$appName\\$moduleName\\controller\\$controller";
            $class = new \ReflectionClass($namespace);

            $res = $this->parseDocComment($class->getDocComment());
            if($res === false){
                $title = $controller;
            }else{
                $title = array_shift($res);
            }
            $this->treeArr[$moduleName]['children'][$key] = [
                    'label'=>$title,
                    'children'=>[]

            ];
            foreach ($class->getMethods() as $method){
                $doc = $method->getDocComment();
                $res = $this->parseDocComment($doc);
                if($method->isProtected()){
                    $node = $moduleName.'/'.$controller;
                    $node = strtolower($node);
                    if($method->name == 'grid'){
                        if($res === false){
                            $auth = false;
                            $nodeData = [
                                'label'=>$node,
                                'rule'=>$node,
                                'is_auth'=>$auth,
                                'is_login'=>$login,
                                'method'=>'get',
                            ];

                        }else{
                            list($title,$auth,$login) = $res;
                            $nodeData = [
                                'label'=>$title,
                                'rule'=>$node,
                                'is_auth'=>$auth,
                                'is_login'=>$login,
                                'method'=>'get',
                            ];
                        }
                        $data[] = $nodeData;
                        $nodeData['mark'] = md5($nodeData['rule'].$nodeData['method']);
                        $this->treeArr[$moduleName]['children'][$key]['children'][] = $nodeData;
                        continue;
                    }elseif ($method->name  == 'form'){
                        if($res === false){
                            $title = '';

                        }else{
                            list($title,$auth,$login) = $res;
                        }
                        $nodeData = [
                            'label'=>$title.'添加',
                            'rule'=>$node.'.rest',
                            'is_auth'=>$auth,
                            'is_login'=>$login,
                            'method'=>'post',
                        ];
                        $data[] = $nodeData;
                        $nodeData['mark'] = md5($nodeData['rule'].$nodeData['method']);
                        $this->treeArr[$moduleName]['children'][$key]['children'][] = $nodeData;
                        $nodeData = [
                            'label'=>$title.'添加页面',
                            'rule'=>$node.'/create.rest',
                            'is_auth'=>false,
                            'is_login'=>$login,
                            'method'=>'get',
                        ];
                        $data[] = $nodeData;
                        $nodeData['mark'] = md5($nodeData['rule'].$nodeData['method']);
                        $nodeData = [
                            'label'=>$title.'修改',
                            'rule'=>$node.'/:id.rest',
                            'is_auth'=>$auth,
                            'is_login'=>$login,
                            'method'=>'put',
                        ];
                        $data[] = $nodeData;
                        $nodeData['mark'] = md5($nodeData['rule'].$nodeData['method']);
                        $this->treeArr[$moduleName]['children'][$key]['children'][] = $nodeData;
                        $nodeData = [
                            'label'=>$title.'修改页面',
                            'rule'=>$node.'/:id/edit.rest',
                            'is_auth'=>false,
                            'is_login'=>$login,
                            'method'=>'get',
                        ];
                        $data[] = $nodeData;
                        $nodeData['mark'] = md5($nodeData['rule'].$nodeData['method']);
                        $nodeData = [
                            'label'=>'删除权限',
                            'rule'=>$node.'/:id.rest',
                            'is_auth'=>$auth,
                            'is_login'=>$login,
                            'method'=>'delete',
                        ];
                        $nodeData['mark'] = md5($nodeData['rule'].$nodeData['method']);
                        $data[] = $nodeData;
                        $this->treeArr[$moduleName]['children'][$key]['children'][] = $nodeData;
                        continue;
                    }elseif ($method->name == 'detail'){
                        if($res === false){
                            $title = '详情';
                            $auth = false;
                        }else{
                            list($title,$auth,$login) = $res;
                        }
                        $nodeData = [
                            'label'=>$title,
                            'rule'=>$node.'/:id.rest',
                            'is_auth'=>$auth,
                            'is_login'=>$login,
                            'method'=>'get',
                        ];
                        $nodeData['mark'] = md5($nodeData['rule'].$nodeData['method']);
                        $data[] = $nodeData;
                        $this->treeArr[$moduleName]['children'][$key]['children'][] = $nodeData;
                        continue;
                    }
                }
                if($method->class == $namespace && $method->isPublic()){
                    $node  = $moduleName.'/'.$controller.'/'.$method->getName();
                    $node = strtolower($node);
                    if($res === false){
                        $nodeData = [
                            'label'=>$node,
                            'rule'=>$node,
                            'is_auth'=>false,
                            'is_login'=>$login,
                            'method'=>'any',
                            'mark'=>md5($node.'any'),
                        ];
                    }else{
                        list($title,$auth,$login,$method) = $res;
                        $nodeData = [
                            'label'=>$title,
                            'rule'=>$node,
                            'is_auth'=>$auth,
                            'is_login'=>$login,
                            'method'=>$method,
                            'mark'=>md5($node.$method),
                        ];
                    }
                    $data[] = $nodeData;
                    if($auth){
                        $this->treeArr[$moduleName]['children'][$key]['children'][] = $nodeData;
                    }

                }

            }
            if(count($this->treeArr[$moduleName]['children'][$key]['children']) == 0){
                unset($this->treeArr[$moduleName]['children'][$key]);

            }
            $this->treeArr[$moduleName]['children']= array_values($this->treeArr[$moduleName]['children']);
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
        //扫描存在配置权限模块控制器下所有文件
        foreach ($modules as $module){
            $moduleName = basename($module);
            //权限模块
            $authNoduleName = config('admin.authModule');

            if(isset($authNoduleName[$moduleName])){
                $authNoduleTitle= $authNoduleName[$moduleName];
                $this->treeArr[$moduleName] = [
                    'label'=>$authNoduleTitle,
                ];
                foreach (glob($module.'/controller/'.'*.php') as $file){
                    if(is_file($file)){
                        $controllerFiles[] =  $file;
                    }
                }
            }
        }
        return $controllerFiles;
    }
}
