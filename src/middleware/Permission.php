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
        if($request->has('submitFromMethod')) {
            $method = $request->param('submitFromMethod');
            if($method != 'form'){
                $pathinfo = $request->controller().'/'.$method;
            }
        }
        $moudel = app('http')->getName() ;
        $node = $moudel. '/' . $pathinfo;
        if (empty($pathinfo) || $pathinfo == 'apiBaseUrl' || $node == 'admin/eadmin/upload') {
            return $next($request);
        }
        //验证权限
        $authNodules = array_keys(config('admin.authModule'));
        if (in_array($moudel,$authNodules) && !AdminService::instance()->check($node, $request->method())) {
            return json(['code'=>44000,'message'=>'没有访问该操作的权限']);
        }
        return $next($request);
    }
}
