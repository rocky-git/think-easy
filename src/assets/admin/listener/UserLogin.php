<?php
declare (strict_types = 1);

namespace app\admin\listener;
use thinkEasy\model\SystemLog;

class UserLogin
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    protected $user;
    public function handle($user)
    {
        $this->user = $user;
        $this->loginSuccess();
        $this->loginLog();
    }
    //登陆成功
    public function loginSuccess(){
        $ip = app()->request->ip();
        $this->user->login_ip = $ip;
        $this->user->login_at = date('Y-m-d H:i:s');
        $this->user->login_num = $this->user->login_num+1;
        $this->user->save();
    }
    //记录日志
    public function loginLog(){
        $node = app('http')->getName() . '/' . app()->request->pathinfo();
        $ip = app()->request->ip();
        SystemLog::create([
            'username'=>$this->user->username,
            'geoip'=>$ip,
            'action'=>'登陆',
            'node'=>$node,
            'content'=>'登陆系统成功',
        ]);
    }
}
