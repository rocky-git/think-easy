<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-26
 * Time: 21:46
 */

namespace thinkEasy\middleware;


use think\facade\App;
use think\Request;
use thinkEasy\service\AdminService;
use thinkEasy\service\TokenService;

class Permission
{
    public function handle(Request $request, \Closure $next)
    {
        $node = app('http')->getName() . '/' . $request->pathinfo();
        if ($node != 'admin/login') {
            //验证登陆状态
            TokenService::instance()->auth();
            //验证权限
            if (!AdminService::instance()->check($node,strtolower($request->method()))) {
                abort(200,  '没有访问该操作的权限！');
            };
        }
        return $next($request);
    }
}