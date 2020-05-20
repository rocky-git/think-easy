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

class UploadService extends Service
{
    /**
     * 本地分片上传
     * @Author: rocky
     * 2019/9/17 18:47
     * @param $file
     * @return \think\response\Json
     * @throws \OSS\Core\OssException
     * @throws \think\Exception
     */
    public function chunkUpload($file)
    {
        $extend = strtolower(pathinfo($this->app->request->post('filename'), PATHINFO_EXTENSION));
        $name =  md5_file($file->getRealPath()) . '.' . $extend;
        $names = str_split(md5($this->app->request->post('filename')), 16);
        $chunks = $this->app->request->post('totalChunks');
        $chunk = $this->app->request->post('chunkNumber');
        if ($chunks == $chunk) {
            $file->move("upload/{$names[0]}", "{$names[1]}{$chunk}", true, false);
            set_time_limit(0);
            $put_filename = "upload/{$name}";
            if (file_exists($put_filename)) {
                unlink($put_filename);
            }
            for ($i = 1; $i <= $chunks; $i++) {
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
            $info = $file->move("upload/{$names[0]}", "{$names[1]}{$chunk}", true, false);
            if($info){
                return true;
            }else{
                return false;
            }
        }
    }
}