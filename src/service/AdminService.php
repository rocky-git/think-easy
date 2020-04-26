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
     * @return bool
     */
    public function check($node)
    {
        $rules = array_column($this->permissions(), 'rule');
        if (in_array($node, $rules)) {
            return true;
        } else {
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
        $permissions = $this->user()->permissions()->column('method', 'node');
        foreach ($nodes as $key => $node) {
            if ($node['is_auth']) {
                if (!isset($permissions[$node['rule']]) || $permissions[$node['rule']] != $node['method']) {
                    unset($nodes[$key]);
                }
            }
        }
        return $nodes;
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