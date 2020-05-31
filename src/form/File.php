<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-19
 * Time: 23:56
 */

namespace thinkEasy\form;


use Overtrue\Flysystem\Qiniu\Plugins\UploadToken;
use think\facade\Filesystem;


class File extends Field
{
    public function __construct($field, $label, array $arguments = [])
    {
        parent::__construct($field, $label, $arguments);
        $this->setAttr('url',request()->domain().'/eadmin/upload');
        $this->disk(config('admin.uploadDisks'));
    }

    /**
     * 唯一文件名
     * @return $this
     */
    public function isUniqidmd5(bool $bool = true){
        if($bool){
            $bool = 'true';
        }else{
            $bool = 'false';
        }
        $this->setAttr(':is-uniqidmd5',$bool);
        return $this;
    }
    /**
     * 显示视频
     * @param $width 宽度
     * @return $this
     */
    public function video($width='auto'){
        $this->setAttr(':width',$width);
        $this->displayType('video');
        return $this;
    }
    /**
     * 显示音频
     * @param $width 宽度
     * @return $this
     */
    public function audio($width='auto'){
        $this->setAttr(':width',$width);
        $this->displayType('audio');
        return $this;
    }
    /**
     * 上传显示方式
     * @param $type image图片,file文件
     */
    public function displayType($type){
        $this->setAttr('display-type',$type);
        return $this;
    }
    public function imageExt(){
        $this->setAttr('accept','image/*');
        return $this;
    }
    /**
     * 限制上传类型
     * @param $vals
     */
    public function ext($vals){
        if(is_string($vals)){
            $vals = explode(',',$vals);
        }
        $vals = array_map(function ($item){
            return ".{$item}";
        },$vals);
        $accept = implode(',',$vals);
        $this->setAttr('accept',$accept);
        return $this;
    }
    /**
     * 多文件上传
     */
    public function multiple(){
        $this->setAttr(':single-file',"false");
        return $this;
    }
    /**
     * 显示尺寸
     * @param $width 宽度
     * @param $height 高度
     * @return $this
     */
    public function size($width,$height){
        $this->setAttr(':width',$width);
        $this->setAttr(':height',$height);
        return $this;
    }
    /**
     * 指定保存目录
     */
    public function saveDir($path){
        if(substr($path,-1) != '/'){
            $path.='/';
        }
        $this->setAttr('save-dir',$path);
        return $this;
    }
    /**
     * 上传存储类型
     * @param $uptype local,qiniu,oss
     */
    public function disk($diskType){
        $config = config('filesystem.disks.'.$diskType);
        $uptype = $config['type'];
        $accessKey = '';
        $accessKeySecret = '';
        $this->setAttr('up-type',$diskType);
        if($uptype == 'qiniu'){
            $this->setAttr('bucket',$config['bucket']);
            $this->setAttr('domain',$config['domain']);
            Filesystem::disk('qiniu')->addPlugin(new UploadToken());
            $this->setAttr('uploadToken',Filesystem::disk('qiniu')->getUploadToken(null,3600*3));
        }elseif ($uptype == 'oss'){
            $this->setAttr('access-key',$config['accessKey']);
            $this->setAttr('secret-key',$config['secretKey']);
            $this->setAttr('bucket',$config['bucket']);
            $this->setAttr('endpoint',$config['endpoint']);
            $this->setAttr('domain',$config['domain']);
            $this->setAttr('region',$config['region']);
        }
        return $this;
    }
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<eadmin-upload {$attrStr}></eadmin-upload>";
        return $html;
    }
}
