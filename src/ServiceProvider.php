<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-03-25
 * Time: 21:43
 */

namespace thinkEasy;


use think\facade\Db;
use think\Service;
use thinkEasy\middleware\Permission;
use thinkEasy\service\FileService;
use thinkEasy\service\PlugService;
use thinkEasy\service\TableViewService;

class ServiceProvider extends Service
{
    public function register()
    {
        //注册上传路由
        FileService::instance()->registerRoute();

        //注册表格视图路由
        TableViewService::instance()->registerRoute();

        //注册插件
        PlugService::instance()->register();
       // $this->app->middleware->route( \thinkEasy\middleware\Permission::class);
    }
    public function boot()
    {
        $this->commands([
            'thinkEasy\command\BuildView',
            'thinkEasy\command\Publish',
            'thinkEasy\command\Plug',
            'thinkEasy\command\Migrate',
            'thinkEasy\command\Seed',
        ]);
    }
}
