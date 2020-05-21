<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-03-25
 * Time: 21:43
 */

namespace thinkEasy;


use think\Service;
use thinkEasy\middleware\Permission;
use thinkEasy\service\FileService;

class AdminServiceRegister extends Service
{
    public function register()
    {
        //注册上传路由
        $this->app->route->post('eadmin/upload', function () {
            $file = $this->app->request->file('file');
            $filename = $this->app->request->post('filename');
            $chunks = $this->app->request->post('totalChunks');
            $chunk = $this->app->request->post('chunkNumber');
            $res = FileService::instance()->chunkUpload($file, $filename, $chunk, $chunks);
            if (!$res) {
                return json(['code'=>999,'message'=>'上传过程出错了'],404);
            } elseif ($res !== true) {
                return json(['code'=>200,'data'=>$res],200);
            }
        });
    }

    public function boot()
    {

    }
}