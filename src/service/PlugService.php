<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-08-09
 * Time: 16:49
 */

namespace thinkEasy\service;


use think\App;
use thinkEasy\Service;

class PlugService extends Service
{
    protected $plugPath = '';
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->plugPath = app()->getRootPath() . 'plugins/';
    }

    /**
     * 获取所有插件
     */
    public function all()
    {
        $pluginDir = $this->app->getRootPath() . '/plugins/';
        $plugins = [];
        foreach (glob($pluginDir . '*') as $file) {
            if (is_dir($file)) {
                $plugins[] = $file;
            }
        }
        $infos = [];
        foreach ($plugins as $plugin) {
            foreach (glob($plugin . '/*Plug.php') as $file) {
                if (is_file($file)) {
                    $plugName = basename($plugin);
                    $plugClassName = basename($file, '.php');
                    $class = "\\plugins\\{$plugName}\\" . $plugClassName;
                    $plug = new $class;
                    $info = $plug->getInfo();
                    $info['class'] = $class;
                    $info['is_install'] = $this->isInstall($class);
                    $infos[] = $info;
                }
            }
        }
        return $infos;
    }

    /**
     * 判断是否已安装
     * @param $class 插件类名
     * @return bool
     */
    public function isInstall($class){
        if(file_exists($this->getPath($class) . '/install.lock')){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 安装
     * @param $class 插件类名
     * @return mixed
     */
    public function install($class)
    {
        $plug = new $class;
        $result = $plug->install();
        if ($result) {
            file_put_contents($this->getPath($class) . '/install.lock','');
        }
        return $result;
    }

    /**
     * 获取插件目录
     * @param $class 插件类名
     * @return string
     */
    public function getPath($class){
        $classArr = explode('\\', $class);
        $plugDir = $this->plugPath  . $classArr[2];
        return $plugDir;
    }
    /**
     * 卸载
     * @param $class
     */
    public function uninstall($class)
    {
        $plug = new $class;
        @unlink($this->getPath($class) . '/install.lock');
        return $plug->uninstall();
    }
}
