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
        foreach (glob($pluginDir . '*') as $file) {
            if (is_dir($file)) {
                $plugins[] = $file;
            }
        }
        $infos = [];
        //扫描存在配置权限模块控制器下所有文件
        foreach ($plugins as $plugin) {
            foreach (glob($plugin . '/*Plug.php') as $file) {
                if (is_file($file)) {
                    $plugName = basename($plugin);
                    $plugClassName = basename($file, '.php');
                    $class = "\\plugins\\{$plugName}\\" . $plugClassName;
                    $plug = new $class;
                    $info = $plug->getInfo();
                    $info['class'] = $class;
                    $infos[] = $info;
                }
            }
        }
        return $infos;
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
        $classArr = explode('\\', $class);
        $plugDir = $this->plugPath  . $classArr[2];
        if ($result) {
            file_put_contents($plugDir . '/install.lock','');
        }
        return $result;
    }

    /**
     * 卸载
     * @param $class
     */
    public function uninstall($class)
    {
        $plug = new $class;
        $classArr = explode('\\', $class);
        $plugDir = $this->plugPath  . $classArr[2];
        @unlink($plugDir . '/install.lock');
        return $plug->uninstall();
    }
}