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

    protected $totalSizeCacheKey;
    public $upType = 'local';

    /**
     * 本地分片上传
     * @param $file 文件对象
     * @param $filename 文件名
     * @param $chunkNumber 分块编号
     * @param $totalChunks 总块数量
     * @param $chunkSize 分片大小
     * @param $totalSize 总文件大小
     * @param $saveDir 指定保存目录
     * @param $isUniqidmd5 是否唯一文件名
     * @param $upType disk
     * @return bool|string
     */
    public function chunkUpload($file, $filename, $chunkNumber, $totalChunks, $chunkSize, $totalSize, $saveDir, bool $isUniqidmd5, $upType = 'local')
    {
        $this->upType = $upType;
        $names = str_split(md5($filename), 16);
        $chunkSaveDir = $names[0];
        if (is_null($file)) {
            if ($totalChunks == 1) {
                if (Filesystem::disk($upType)->has($saveDir . $filename)) {
                    return $this->url($saveDir . $filename);
                } else {
                    return false;
                }
            } elseif ($isUniqidmd5 == false) {
                if (Filesystem::disk($upType)->has($saveDir . $filename)) {
                    return $this->url($saveDir . $filename);
                } else {
                    return $this->checkChunkExtis($filename, $chunkSaveDir, $chunkNumber, $chunkSize, $totalSize);
                }
            } else {
                return $this->checkChunkExtis($filename, $chunkSaveDir, $chunkNumber, $chunkSize, $totalSize);
            }
        } else {
            if ($totalChunks == 1) {
                //分片总数量1直接保存
                if (substr($saveDir, -1) == '/') {
                    $saveDir = substr($saveDir, 0, -1);
                }
                if ($isUniqidmd5) {
                    return $this->upload($file,null, $saveDir);
                } else {
                    return $this->upload($file, $filename,$saveDir);
                }
            } else {

                $this->totalSizeCacheKey = md5($filename . 'totalSize');

                $chunkName = $names[1] . $chunkNumber;
                //写分片文件
                $res = Filesystem::disk($this->upType)->putFileAs($chunkSaveDir, $file, $chunkName);

                //判断分片数量是否和总数量一致,一致就合并分片文件

                if ($this->getChunkDircounts($chunkSaveDir) == $totalChunks) {
                    if (!Cache::has(md5($filename))) {
                        Cache::set(md5($filename), 1, 10);
                        $url = $this->merge($chunkSaveDir, $filename, $totalChunks, $saveDir, $isUniqidmd5);
                        Cache::delete(md5($filename));
                        return $url;
                    }
                    return true;
                }
                if ($res === false) {
                    return false;
                } else {

                    if (Cache::has($this->totalSizeCacheKey)) {
                        $totalSizeCache = Cache::get($this->totalSizeCacheKey);
                        if ($totalSizeCache != $totalSize) {
                            Cache::set($this->totalSizeCacheKey, $totalSize, 3600 * 3);
                        }
                    } else {
                        Cache::set($this->totalSizeCacheKey, $totalSize, 3600 * 3);
                    }
                    return true;
                }
            }
        }
    }

    /**
     * 上传文件
     * @param $file 文件对象
     * @param $fileName 文件名
     * @param $saveDir 保存目录
     * @return  bool|string
     */
    public function upload($file, $fileName = null,$saveDir = 'editor')
    {
        if(empty($fileName)){
            $saveName = Filesystem::disk($this->upType)->putFile($saveDir, $file, 'uniqid');
        }else{
            $saveName = Filesystem::disk($this->upType)->putFileAs($saveDir, $file, $fileName);
        }
        if ($saveName) {
            return $this->url($saveName);
        } else {
            return false;
        }
    }

    /**
     * 获取目录下文件数量
     * @param $chunkSaveDir
     * @return int
     */
    protected function getChunkDircounts($chunkSaveDir)
    {
        $dir = Filesystem::disk($this->upType)->path('');
        $chunkSaveDir = $dir . $chunkSaveDir;
        $handle = opendir($chunkSaveDir);
        $i = 0;
        while (false !== $file = (readdir($handle))) {
            if ($file !== '.' && $file != '..') {
                $i++;
            }
        }
        closedir($handle);
        return $i;
    }

    /**
     * 判断文件分片是否存在实现秒传
     * @param $filename 文件名
     * @param $chunkSaveDir 分片保存目录
     * @param $chunkNumber 第几片
     * @param $chunkSize 分片大小
     * @param $totalChunks 总大小
     * @return bool
     */
    protected function checkChunkExtis($filename, $chunkSaveDir, $chunkNumber, $chunkSize, $totalSize)
    {
        $this->totalSizeCacheKey = md5($filename . 'totalSize');
        if (Cache::has($this->totalSizeCacheKey)) {
            $totalSizeCache = Cache::get($this->totalSizeCacheKey);
            if ($totalSizeCache != $totalSize) {
                return false;
            }
        }
        $dir = Filesystem::disk($this->upType)->path('');
        $names = str_split(md5($filename), 16);
        $chunkSaveDir = $dir . $chunkSaveDir;
        $filenameChunk = $chunkSaveDir . DIRECTORY_SEPARATOR . $names[1] . $chunkNumber;
        if (file_exists($filenameChunk) && filesize($filenameChunk) == $chunkSize) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取访问路径
     * @param $name 文件名
     * @return string
     */
    public function url($name)
    {
        $config = Filesystem::disk($this->upType)->getConfig();
        if($this->upType == 'safe'){
            return $name;
        }else{
            return $this->app->request->domain() . $config->get('url') . DIRECTORY_SEPARATOR . $name;
        }

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
    protected function merge($chunkSaveDir, $filename, $totalChunks, $saveDir, $isUniqidmd5)
    {
        set_time_limit(0);
        $dir = Filesystem::disk($this->upType)->path('');
        $chunkSaveDir = $dir . $chunkSaveDir;
        $extend = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($isUniqidmd5 == 'true') {
            $saveName = $saveDir . md5(uniqid() . $filename) . '.' . $extend;
        } else {
            $saveName = $saveDir . $filename;
        }
        $put_filename = $dir . DIRECTORY_SEPARATOR . $saveName;
        if (file_exists($put_filename)) {
            unlink($put_filename);
        }
        $names = str_split(md5($filename), 16);
        for ($i = 1; $i <= $totalChunks; $i++) {
            $filenameChunk = $chunkSaveDir . DIRECTORY_SEPARATOR . $names[1] . $i;
            $fileData = file_get_contents($filenameChunk);
            file_exists(dirname($put_filename)) || mkdir(dirname($put_filename), 0755, true);
            $res = file_put_contents($put_filename, $fileData, FILE_APPEND);
        }
        array_map('unlink', glob("{$chunkSaveDir}/*"));
        rmdir($chunkSaveDir);
        if ($res) {
            return $this->url($saveName);
        } else {
            return false;
        }
    }
}