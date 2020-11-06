<?php


namespace thinkEasy\model;


use think\Model;
use thinkEasy\service\AdminService;

class SystemTable extends BaseModel
{
    protected $json = ['fields','all_fields'];
    protected $jsonAssoc = true;
    public static function onBeforeInsert($data)
    {
        $data['uid'] = AdminService::instance()->id();
    }

    protected function setGridAttr($val)
    {
        return md5($val);
    }

}
