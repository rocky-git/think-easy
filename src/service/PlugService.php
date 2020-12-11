<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-08-09
 * Time: 16:49
 */

namespace thinkEasy\service;

use Composer\Autoload\ClassLoader;
use think\App;
use think\facade\Console;
use think\facade\Db;
use think\helper\Arr;
use thinkEasy\Service;

class PlugService extends Service
{
    protected $plugPathBase = '';
    protected $plugPaths = [];
    protected $plugs = [];
    protected static $loader;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->plugPathBase = app()->getRootPath() . config('admin.extension.dir', 'eadmin-plugs') ;

        foreach (glob($this->plugPathBase . '/*') as $file) {

            if (is_dir($file)) {
                foreach (glob($file . '/*') as $file) {
                    $this->plugPaths[] = $file;
                }
            }
        }

    }

    /**
     * 获取 composer 类加载器.
     *
     * @return ClassLoader
     */
    public function loader()
    {
        if (!static::$loader) {
            static::$loader = include $this->app->getRootPath() . '/vendor/autoload.php';
        }
        return static::$loader;
    }

    /**
     * 注册扩展
     */
    public function register()
    {
        $loader = $this->loader();
        foreach ($this->plugPaths as $plugPaths) {
            $file = $plugPaths . DIRECTORY_SEPARATOR . 'composer.json';
            $arr = json_decode(file_get_contents($file), true);
            $psr4 = Arr::get($arr, 'autoload.psr-4');
            $name = Arr::get($arr,'name');
            if($this->status($name)){
                if ($psr4) {
                    foreach ($psr4 as $namespace => $path) {
                        $path = $plugPaths . '/' . trim($path, '/') . '/';
                        $loader->addPsr4($namespace, $path);
                    }
                }
                $serviceProvider = Arr::get($arr, 'extra.e-admin');
                if ($serviceProvider) {
                    $this->app->register($serviceProvider);
                }
            }
        }
    }

    /**
     * 获取所有插件
     */
    public function all()
    {
        $names = [];
        foreach ($this->plugPaths as $plugPath) {
            if($this->checkFiles($plugPath)){
                $info = $this->getInfo($plugPath);
                $names[] = $info['name'];
                $this->plugs[] = $info;
            }
        }
        Db::name('system_plugs')->whereNotIn('name',$names)->delete();
        return $this->plugs;
    }
    protected function getInfo($dir)
    {
        $arr = json_decode(file_get_contents($dir. '/composer.json'), true);
        $version = include $dir.'/version.php';
        $version = array_column($version,'version');
        $version = array_shift($version);
        $authors = array_column(Arr::get($arr,'authors'),'name');
        $authors = implode(',',$authors);
        $emails = array_column(Arr::get($arr,'authors'),'email');
        $emails = implode(',',$emails);
        $name = Arr::get($arr,'name');
        $status = $this->status($name);
        return [
            'name' => $name,
            'description' => Arr::get($arr,'description'),
            'author' => $authors,
            'email' =>$emails,
            'status'=> $status ?? false,
            'install'=> is_null($status) ? false : true,
            'version' => $version,
            'path'=>$dir,
        ];
    }

    /**
     * 插件状态
     * @param $name 插件名称
     * @return mixed
     */
    public function status($name){
        return Db::name('system_plugs')->where('name',$name)->value('status');
    }
    /**
     * 启用禁用
     * @param $name 插件名称
     * @param $status 状态
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function enable($name,$status){
        return Db::name('system_plugs')->where('name',$name)->update(['status'=>$status]);
    }
    /**
     * 校验扩展包内容是否正确.
     *
     * @param $directory
     *
     * @return bool
     */
    protected function checkFiles($directory)
    {
        if (
            ! is_dir($directory.'/src')
            || ! is_file($directory.'/composer.json')
            || ! is_file($directory.'/version.php')
        ) {
            return false;
        }
        return true;
    }
    protected function dataMigrate($cmd,$path){
        $migrations = $path.'/src/database'. DIRECTORY_SEPARATOR . 'migrations';
        if(is_dir($migrations)){
            Console::call('migrate:eadmin',['cmd'=>$cmd,'path'=> $migrations]);
        }
        return true;
    }
    protected function getName($path){
        $info = $this->getInfo($path);
        return $info['name'];
    }
    /**
     * 安装
     * @param $path 插件目录
     * @return mixed
     */
    public function install($path)
    {
        $this->dataMigrate('run',$path);
        $seed = $path.'/src/database'. DIRECTORY_SEPARATOR . 'seeds';
        if(is_dir($seed)){
            Console::call('seed:eadmin',['path'=> $seed]);
        }
        Db::name('system_plugs')->insert([
           'name'=> $this->getName($path),
        ]);
        return true;
    }
    /**
     * 卸载
     * @param $path
     */
    public function uninstall($path)
    {
        Db::name('system_plugs')->where('name',$this->getName($path))->delete();
        return $this->dataMigrate('rollback',$path);;
    }

}
