<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-21
 * Time: 00:05
 */

namespace thinkEasy\service;


use think\facade\Filesystem;
use thinkEasy\Service;

class FileService extends Service
{

    /**
     * 本地分片上传
     * @param $file
     * @param $filename
     * @param $chunkNumber
     * @param $totalChunks
     * @return bool|string
     */
    public function chunkUpload($file,$filename,$chunkNumber,$totalChunks)
    {
        $extend = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $name =  md5_file($file->getRealPath()) . '.' . $extend;
        $names = str_split(md5($filename), 16);
        if ($totalChunks == $chunkNumber) {
            $file->move("upload/{$names[0]}", "{$names[1]}{$chunkNumber}", true, false);
            set_time_limit(0);
            $put_filename = "upload/{$name}";
            if (file_exists($put_filename)) {
                unlink($put_filename);
            }
            for ($i = 1; $i <= $totalChunks; $i++) {
                $filenameChunk = "upload/{$names[0]}/" . "{$names[1]}{$i}";
                $fileData = file_get_contents($filenameChunk);
                file_exists(dirname($put_filename)) || mkdir(dirname($put_filename), 0755, true);
                $res = file_put_contents($put_filename, $fileData, FILE_APPEND);
            }
            array_map('unlink', glob("upload/{$names[0]}/*"));
            rmdir("upload/{$names[0]}");
            if($res){
                return $this->app->request->domain().'/'.$put_filename;
            }else{
                return false;
            }
        } else {
            $info = $file->move("upload/{$names[0]}", "{$names[1]}{$chunkNumber}", true, false);
            if($info){
                return true;
            }else{
                return false;
            }
        }
    }
}