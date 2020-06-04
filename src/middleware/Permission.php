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

        $pathinfo = $request->pathinfo();
        if (empty($pathinfo) || $pathinfo == 'apiBaseUrl') {
            return $next($request);
        }
        $moudel = app('http')->getName() ;
        $node = $moudel. '/' . $pathinfo;
        //验证权限
        $authNodules = array_keys(config('admin.authNodule'));
        if (in_array($moudel,$authNodules) && !AdminService::instance()->check($node, $request->method())) {
            abort(200, '没有访问该操作的权限！');
        }
        return $next($request);
    }
}