<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-03-25
 * Time: 21:43
 */

namespace thinkEasy;


use think\facade\Db;
use think\route\Resource;
use think\Service;
use thinkEasy\controller\Backup;
use thinkEasy\controller\Log;
use thinkEasy\controller\Menu;
use thinkEasy\controller\Notice;
use thinkEasy\controller\Plug;
use thinkEasy\middleware\Permission;
use thinkEasy\service\FileService;
use thinkEasy\service\PlugService;
use thinkEasy\service\TableViewService;

class ServiceProvider extends Service
{
    public function register()
    {
        $this->registerView();
        //注册上传路由
        FileService::instance()->registerRoute();
        //注册表格视图路由
        TableViewService::instance()->registerRoute();
        //注册插件
        PlugService::instance()->register();
        $this->app->middleware->route( \thinkEasy\middleware\Permission::class);
    }

    protected function registerView(){
        //入口加载
        $this->app->route->get('/',function (){
            return file_get_contents(__DIR__.'/view/index.vue');
        });
        //菜单管理
        $this->app->route->resource('menu',Menu::class);
        //日志调试
        $this->app->route->post('log/logData',Log::class.'@logData');
        $this->app->route->get('log/debug',Log::class.'@debug');
        $this->app->route->post('log/remove',Log::class.'@remove');
        //插件
        $this->app->route->get('plug/add',Plug::class.'@add');
        $this->app->route->get('plug',Plug::class.'@index');
        $this->app->route->put('plug/enable',Plug::class.'@enable');
        $this->app->route->put('plug/install',Plug::class.'@install');
        $this->app->route->resource('plug',Plug::class);
        //消息通知
        $this->app->route->get('notice/notification',Notice::class.'@notification');
        $this->app->route->post('notice/system',Notice::class.'@system');
        $this->app->route->post('notice/reads',Notice::class.'@reads');
        $this->app->route->delete('notice/clear',Notice::class.'@clear');
        //数据库备份
        $this->app->route->get('backup/config',Backup::class.'@config');
        $this->app->route->put('backup/add',Backup::class.'@add');
        $this->app->route->put('backup/reduction',Backup::class.'@reduction');
        $this->app->route->resource('backup',Backup::class);
        
        $this->app->route->resource(':controller',':controller')->ext('rest');
    }
    public function boot()
    {
        $this->commands([
            'thinkEasy\command\BuildView',
            'thinkEasy\command\Publish',
            'thinkEasy\command\Plug',
            'thinkEasy\command\Migrate',
            'thinkEasy\command\Seed',
            'thinkEasy\command\Install',
        ]);
    }
}
