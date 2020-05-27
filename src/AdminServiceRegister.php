<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-03-25
 * Time: 21:43
 */

namespace thinkEasy;


use think\facade\Db;
use think\Service;
use thinkEasy\middleware\Permission;
use thinkEasy\service\FileService;

class AdminServiceRegister extends Service
{
    public function register()
    {
        //注册上传路由
        $this->app->route->any('eadmin/upload', function () {
            $file = $this->app->request->file('file');
            $filename = $this->app->request->param('filename');
            $chunks = $this->app->request->param('totalChunks');
            $chunk = $this->app->request->param('chunkNumber');
            $saveDir = $this->app->request->param('saveDir','/');
            $totalSize = $this->app->request->param('totalSize');
            $chunkSize = $this->app->request->param('chunkSize');
            $isUniqidmd5 = $this->app->request->param('isUniqidmd5',false);
            $upType = $this->app->request->param('upType','local');
            if($isUniqidmd5 == 'true'){
                $isUniqidmd5 = true;
            }else{
                $isUniqidmd5 = false;
            }
            if($this->app->request->method() == 'POST' && empty($chunk)){
                $res = FileService::instance()->upload($file,$upType);
                if (!$res) {
                    return json(['code'=>999,'message'=>'上传失败'],404);
                } else{
                    return json(['code'=>200,'data'=>$res],200);
                }
            }


            $res = FileService::instance()->chunkUpload($file, $filename, $chunk,$chunks, $chunkSize,$totalSize,$saveDir,$isUniqidmd5,$upType);
            if($this->app->request->method() == 'POST'){
                if (!$res) {
                    return json(['code'=>999,'message'=>'上传失败'],404);
                } elseif ($res !== true) {
                    return json(['code'=>200,'data'=>$res],200);
                }elseif ($res === true) {
                    return json(['code'=>200,'message'=>'分片上传成功'],201);
                }
            }else{
                if ($res) {
                    return json(['code'=>200,'data'=>$res,'message'=>'秒传成功'],202);
                } else{
                    return json(['code'=>200,'message'=>'请重新上传分片'],203);
                }
            }

        });

        if (!function_exists('sysconf')) {
            /**
             * 配置系统参数
             * @param string $name 参数名称
             * @param boolean $value 无值为获取
             * @return string|boolean
             * @throws \think\Exception
             * @throws \think\exception\PDOException
             */
            function sysconf($name, $value = null)
            {
                if(is_null($value)){
                    $value =  Db::name('SystemConfig')->where('name',$name)->value('value');
                    if(is_null($value)){
                        return '';
                    }else{
                        return $value;
                    }
                }else{
                    $sysconfig = Db::name('SystemConfig')->where('name',$name)->find();
                    if($sysconfig){
                        return Db::name('SystemConfig')->where('name',$name)->update(['value'=>$value]);
                    }else{
                        return Db::name('SystemConfig')->insert([
                            'name'=>$name,
                            'value'=>$value,
                        ]);
                    }
                }
            }
        }
    }
    public function boot()
    {
        $this->commands([
            'thinkEasy\command\BuildView',
        ]);
    }
}
