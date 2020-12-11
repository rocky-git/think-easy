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

    /**
     * 获取所有插件
     */
    public function all()
    {
        foreach ($this->plugPaths as $plugPath) {
            if($this->checkFiles($plugPath)){
                $this->plugs[] = $this->getInfo($plugPath);
            }
        }

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

        return [
            'name' => Arr::get($arr,'name'),
            'description' => Arr::get($arr,'description'),
            'author' => $authors,
            'email' =>$emails,
            'is_install'=>true,
            'version' => $version,
            'path'=>$dir,
        ];
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
    /**
     * 判断是否已安装
     * @param $class 插件类名
     * @return bool
     */
    public function isInstall($class)
    {
        if (file_exists($this->getPath($class) . '/install.lock')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 安装
     * @param $path 插件目录
     * @return mixed
     */
    public function install($path)
    {
        $migrations = $path.'/src/database'. DIRECTORY_SEPARATOR . 'migrations';
        if(is_dir($migrations)){
            Console::call('migrate:eadmin',['cmd'=>'run','path'=> $migrations]);
        }
        $seed = $path.'/src/database'. DIRECTORY_SEPARATOR . 'seeds';
        if(is_dir($seed)){
            Console::call('seed:eadmin',['path'=> $seed]);
        }
        return true;
    }
    protected function database($path){
        $migrations = $path.'/src/database'. DIRECTORY_SEPARATOR . 'migrations';
        if(is_dir($migrations)){
            Console::call('migrate:eadmin',['cmd'=>'run','path'=> $migrations]);
        }
        return true;
    }
    /**
     * 获取插件目录
     * @param $path 插件目录
     * @return string
     */
    public function getPath($class)
    {
        $classArr = explode('\\', $class);
        $plugDir = $this->plugPath . $classArr[2];
        return $plugDir;
    }
    /**
     * 卸载
     * @param $path
     */
    public function uninstall($path)
    {
        $migrations = $path.'/src/database'. DIRECTORY_SEPARATOR . 'migrations';
        if(is_dir($migrations)) {
            Console::call("migrate:eadmin", [
                'cmd' => "rollback",
                'path' => $migrations,
            ]);
        }
        return true;
    }
}
