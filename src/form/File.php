<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-19
 * Time: 23:56
 */

namespace thinkEasy\form;


class File extends Field
{
    public function __construct($field, $label, array $arguments = [])
    {
        parent::__construct($field, $label, $arguments);
        $this->setAttr('url',request()->domain().'/eadmin/upload');
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
        $this->setAttr('up-type',$uptype);
        if($uptype == 'qiniu'){
            $this->setAttr('access-key',$config['accessKey']);
            $this->setAttr('secret-key',$config['secretKey']);
            $this->setAttr('bucket',$config['bucket']);
        }elseif ($uptype == 'oss'){
            $this->setAttr('access-key',$config['accessKey']);
            $this->setAttr('secret-key',$config['secretKey']);
            $this->setAttr('bucket',$config['bucket']);
            $this->setAttr('endpoint',$config['endpoint']);
            
        }
    }
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<eadmin-upload {$attrStr}></eadmin-upload>";
        return $html;
    }
}