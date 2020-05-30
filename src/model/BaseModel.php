<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-09
 * Time: 00:15
 */

namespace thinkEasy\model;


use think\Model;


class BaseModel extends Model
{
    protected $autoWriteTimestamp = 'datetime';
    protected $globalScope = ['base'];
    public function scopeBase($query)
    {
        $id = $query->getPk();
        $tableFields = $query->getTableFields();
        //默认排序
        if (in_array('sort', $tableFields)) {
            $query->order('sort asc')->order("{$id} desc");
        } else {
            $query->order("{$id} desc");
        }
        //默认不包含软删除数据
        if (in_array('delete_time', $tableFields)) {
            $query->whereNull('delete_time');
        }
    }
}