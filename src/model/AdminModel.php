<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-25
 * Time: 17:17
 */

namespace thinkEasy\model;


class AdminModel extends BaseModel
{
    public function __construct(array $data = [])
    {
        $this->table = config('admin.system_user_table');
        parent::__construct($data);
    }
    protected function setPasswordAttr($val){
        return password_hash($val,PASSWORD_DEFAULT);
    }
    //权限
    public function permissions(){
        return SystemAuthNode::whereIn('auth',$this->roles()->column('id'))->select();
    }
    //角色组
    public function roles(){
        return $this->belongsToMany('system_auth','system_user_auth','role_id','user_id');
    }
}