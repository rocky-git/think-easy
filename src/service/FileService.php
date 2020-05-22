<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-21
 * Time: 00:05
 */

namespace thinkEasy\service;


use think\facade\Cache;
use think\facade\Filesystem;
use thinkEasy\Service;

class FileService extends Service
{

    /**
     * 本地分片上传
     * @param $file 文件对象
     * @param $filename 文件名
     * @param $chunkNumber 分块编号
     * @param $totalChunks 总块数量
     * @param $saveDir 指定保存目录
     * @param $isUniqidmd5 是否唯一文件名
     * @return bool|string
     */
    public function chunkUpload($file,$filename,$chunkNumber,$totalChunks,$saveDir,bool $isUniqidmd5)
    {
        $names = str_split(md5($filename), 16);
        $chunkSaveDir = $names[0];
        if($totalChunks == 1){
            //分片总数量1直接保存
            if(substr($saveDir,-1) == '/'){
                $saveDir = substr($saveDir, 0, -1);
            }
            if($isUniqidmd5){
                $saveName = Filesystem::disk('local')->putFile($saveDir,$file,'uniqid');
                $fileNamerule = 'uniqid';
            }else{
                $saveName = Filesystem::disk('local')->putFileAs($saveDir,$file,$filename);
            }
            return $this->url($saveName);
        }else{
            $chunkName = $names[1].$chunkNumber;
            //写分片文件
            $res = Filesystem::disk('local')->putFileAs($chunkSaveDir,$file,$chunkName);
            if(Cache::has($filename)){
                $cacheChunk = unserialize(Cache::get($filename));
            }else{
                $cacheChunk = [];
            }
            //判断分片数量是否和总数量一致,一致就合并分片文件
            $uploadedChunkNum = count($cacheChunk);
            if($uploadedChunkNum == $totalChunks){
                Cache::delete($filename);
                return $this->merge($chunkSaveDir,$filename,$totalChunks,$saveDir,$isUniqidmd5);
            }
            if($res === false){
                return false;
            }else{
                //写入成功记录分片数量
                if(Cache::has($filename)){
                    $cacheChunk =  unserialize(Cache::get($filename));
                    array_push($cacheChunk,$chunkName);
                    $cacheChunk = array_filter($cacheChunk);
                    Cache::set($filename,serialize($cacheChunk),3600*3);
                }else{
                    $cacheChunk = [];
                    array_push($cacheChunk,$chunkName);
                    Cache::set($filename,serialize($cacheChunk),3600*3);
                }
                return true;
            }
        }
    }

    /**
     * 获取访问路径
     * @param $name 文件名
     * @return string
     */
    public function url($name){
        $config = Filesystem::disk('local')->getConfig();
        return $this->app->request->domain().$config->get('url').DIRECTORY_SEPARATOR.$name;
    }
    /**
     * 合并分片文件
     * @param $chunkSaveDir 分片保存目录
     * @param $filename 文件名
     * @param $totalChunks
     * @param $saveDir 指定保存目录
     * @param $isUniqidmd5 是否唯一文件名
     * @return bool|string
     */
    protected function merge($chunkSaveDir,$filename,$totalChunks,$saveDir,$isUniqidmd5){
        set_time_limit(0);
        $dir =  Filesystem::disk('local')->path('');
        $chunkSaveDir = $dir.$chunkSaveDir;
        $extend = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if($isUniqidmd5 == 'true'){
            $saveName = $saveDir.md5(uniqid().$filename) . '.' . $extend;
        }else{
            $saveName = $saveDir.$filename;
        }
        $put_filename =  $dir.DIRECTORY_SEPARATOR.$saveName;
        if (file_exists($put_filename)) {
            unlink($put_filename);
        }
        $names = str_split(md5($filename), 16);
        for ($i = 1; $i <= $totalChunks; $i++) {
            $filenameChunk = $chunkSaveDir.DIRECTORY_SEPARATOR.$names[1].$i;
            $fileData = file_get_contents($filenameChunk);
            file_exists(dirname($put_filename)) || mkdir(dirname($put_filename), 0755, true);
            $res = file_put_contents($put_filename, $fileData, FILE_APPEND);
        }
        array_map('unlink', glob("{$chunkSaveDir}/*"));
        rmdir($chunkSaveDir);
        if($res){
            return $this->url($saveName);
        }else{
            return false;
        }
    }
}