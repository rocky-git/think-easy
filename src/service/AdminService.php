<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-23
 * Time: 22:36
 */

namespace thinkEasy\service;

use thinkEasy\Service;

/**
 * 系统权限服务
 * Class AuthService
 * @package thinkEasy\service
 */
class AdminService extends Service
{
    /**
     * 获取权限菜单
     */
    public function menus()
    {
        $menus = MenuService::instance()->all();

        if ($this->id() != config('admin.admin_auth_id')) {
            $pids = array_column($menus, 'pid');
            foreach ($menus as $key => $menu) {
                if (in_array($menu['id'], $pids)) {
                    $rulesMenus = $this->findMenuChildren($menu['id']);
                    $findMenu = false;
                    foreach ($rulesMenus as $checkMenu){
                        if ($this->check($checkMenu['url'])) {
                            $findMenu = true;
                            break;
                        }
                    }
                    if($findMenu){
                       continue;
                    }
                }
                if (preg_match("/^(https?:|mailto:|tel:)/", $menu['url'])) {
                    continue;
                }
                if (!$this->check($menu['url'])) {
                    unset($menus[$key]);
                }
            }
        }

        return MenuService::instance()->treeMenus($menus);
    }
    protected function findMenuChildren($menu_id,$menuList=[]){
        $menuList = [];
        foreach (MenuService::instance()->all() as $menu){
            if($menu['pid'] == $menu_id){
                $menuList[] = $menu;
                $menuList = array_merge($menuList,$this->findMenuChildren($menu['id'],$menuList)); //注意写$data 返回给上级
            }
        }
        if(count($menuList)>0){
            return $menuList;
        }else{
            return [];
        }
    }

    /**
     * 判断权限节点
     * @param $node 节点
     * @method $method 请求方法
     * @return bool
     */
    public function check($node,$method='')
    {
        if ($this->id() == config('admin.admin_auth_id')) {
            return true;
        }

        $method = strtolower($method);
        $node = strtolower($node);
        $ext = pathinfo($node, PATHINFO_EXTENSION);

        if(strpos($node,'edit.rest')){
            $node = preg_replace("/(.+)\/(.+)\/(.+)\/edit\.rest$/U","\\1/\\2/:id/edit.rest",$node);
        }elseif(strpos($node,'create.rest')){
            $node = preg_replace("/(.+)\/(.+)\/(.+)\/create\.rest$/U","\\1/\\2/create.rest",$node);
        }elseif($ext == 'rest'){

            $node = preg_replace("/(.+)\/(.+)\/(.+)\.rest$/U","\\1/\\2/:id.rest",$node);
        }

        $permissions = $this->permissions();

        if(empty($method)){
            $rules = array_column($permissions, 'rule');
            if (in_array($node, $rules)) {
                return true;
            } else {
                return false;
            }
        }else{
            foreach ($permissions as $permission){
                if($permission['rule'] == $node && ($permission['method'] == $method || $permission['method'] == 'any')){
                    if($permission['is_login']){
                        TokenService::instance()->auth();
                    }
                    return true;
                }
            }
            return false;
        }

    }

    /**
     * 获取当前用户
     * @return mixed
     */
    public function user()
    {
        return TokenService::instance()->user();
    }

    /**
     * 获取权限节点
     * @return mixed
     */
    public function permissions()
    {
        $nodes = NodeService::instance()->all();
        if($this->user()){
            $permissions = $this->user()->permissions();
        }else{
            $permissions = [];
        }
        $newNodes = [];
        foreach ($nodes as $key => $node) {
            if ($node['is_auth']) {
                foreach ($permissions as $permission){
                    if($permission['node'] == $node['rule'] && ($permission['method'] == $node['method'] || $node['method'] == 'any')){
                        $newNodes[] = $nodes[$key];
                    }
                }
            }else{
                $newNodes[] = $nodes[$key];
            }
        }
        
        return $newNodes;
    }

    /**
     * 获取用户角色组
     */
    public function roles()
    {
        return $this->user()->roles;
    }

    /**
     * 获取当前登陆用户id
     * @return string
     */
    public function id()
    {
        return TokenService::instance()->id();
    }
}