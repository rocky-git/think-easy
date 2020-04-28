<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-11
 * Time: 14:21
 */

namespace thinkEasy\service;

use think\facade\Db;
use thinkEasy\Service;

/**
 * 系统菜单服务
 * Class MenuService
 * @package app\admin\service
 */
class MenuService extends Service
{
    //菜单列表缓存key
    protected $cacheKey = 'eadmin_menu_list';
    public function all(){
        
        if($this->app->cache->has($this->cacheKey)){
            return unserialize($this->app->cache->get($this->cacheKey));
        }else{
            $data = Db::name('system_menu')->where('status',1)->order('sort asc,id asc')->select()->toArray();
            $this->app->cache->set($this->cacheKey,serialize($data));
            return $data;
        }
    }
    public function treeMenus($data){
        return $this->getTree($data);
    }

    public function listOptions(){
        $data = Db::name('system_menu')->where('status',1)->order('sort asc,id asc')->select();
        $menusList = $this->getTreeLevel($data);
        foreach ($menusList as &$value){
            $value['label'] = str_repeat("　├　", $value['level']+1).$value['name'];
        }
        return $menusList;
    }
    protected function getTree($list, $pid = 0)
    {
        $tree = [];
        if (!empty($list)) {
            $newList = [];
            foreach ($list as $k => $v) {
                $params = [];
                if(!empty($v['params'])){
                    $paramsArrs = explode('&',$v['params']);
                    foreach ($paramsArrs as $paramsArr){
                        list($key,$value) = explode('=',$paramsArr);
                        $params[] = [
                            'key'=>$key,
                            'value'=>$value
                        ];
                    }
                }
                $v['meta'] = ['title'=>$v['name'],'icon'=>$v['icon'],'id'=>$v['id'],'pid'=>$v['pid'],'params'=>$params];
                $v['component'] = 'Layout';
                $v['name'] = 'tag_'.$v['id'];
                $preg = "/^(https?:|mailto:|tel:)/";

                if($v['url'] == '#'){
                    $v['path'] = $v['name'];
                }else{
                    if(preg_match($preg,$v['url'])){
                        $v['path'] = $v['url'];
                    }else{
                        $v['path'] = DIRECTORY_SEPARATOR.$v['url'];
                    }
                }
                $newList[$v['id']] = $v;
            }
            $routers = array_values($newList);
            $resourceRouter = [];
            foreach ($routers as $router){
                $createRouter = $router;
                $createRouter['path'] = $createRouter['path'].'/create';
                $createRouter['name'] = $createRouter['name'].'$create';
                $createRouter['meta']['id'] = -1;
                $editRouter = $router;
                $editRouter['path'] = $editRouter['path'].'/:id/edit';
                $editRouter['name'] = $editRouter['name'].'$edit';
                $editRouter['meta']['id'] = -1;
                $resourceRouter[] = $router;
                $resourceRouter[] = $createRouter;
                $resourceRouter[] = $editRouter;
            }
            foreach ($newList as $value) {
                if ($pid == $value['pid']) {
                    $tree[] = &$newList[$value['id']];
                } elseif (isset($newList[$value['pid']])) {
                    $newList[$value['pid']]['children'][] = &$newList[$value['id']];
                }
            }
        }
        return [$resourceRouter,$tree];
    }
    protected function getTreeLevel($array, $pid =0, $level = 0){
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $listMenus = [];
        foreach ($array as $key => $value){
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['pid'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $listMenus[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                $this->getTreeLevel($array, $value['id'], $level+1);
            }
        }
        return $listMenus;
    }
}
