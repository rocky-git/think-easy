<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-26
 * Time: 21:46
 */

namespace thinkEasy\middleware;


use think\Request;
use thinkEasy\service\AdminService;
use thinkEasy\service\TokenService;

class Permission
{
    public function handle(Request $request, \Closure $next)
    {
        //验证登陆状态
        TokenService::instance()->auth();
        //验证权限
        if (!AdminService::instance()->check()) {

        }
        return $next($request);
    }
}