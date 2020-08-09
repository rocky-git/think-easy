<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-08-09
 * Time: 15:54
 */

namespace thinkEasy;
/**
 * 插件
 * Class Plug
 * @package thinkEasy
 */
abstract class Plug
{
    protected $info = [
        'name'=>'插件名称',
        'description'=>'插件描述',
        'version'=>'1.0.0',
        'author'=>'作者',
    ];
    //获取插件信息
    final function getInfo(){
        return $this->info;
    }
     function getPath(){
        return $this->path;
    }
    protected function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    //循环删除目录和文件函数
    protected function delDirAndFile($dirName)
    {
        if ($handle = opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item")) {
                        $this->delDirAndFile("$dirName/$item");
                    } else {
                        unlink("$dirName/$item");
                    }
                }
            }
            closedir($handle);
            rmdir($dirName);
        }
    }
    //获取插件描述
    final function getDescription(){
        return $this->getDescription();
    }
    //安装
    abstract function install();
    //卸载
    abstract function uninstall();
}