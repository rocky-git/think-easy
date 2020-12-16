<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-25
 * Time: 16:33
 */

use thinkEasy\model\AdminModel;

return [
    //超级管理员id
    'admin_auth_id' => 1,
    //令牌key
    'token_key' => 'QsoYEClMJsgOSWUBkSCq26yWkApqSuH3',
    //令牌过期时间
    'token_expire' => 7200,
    //是否唯一登陆
    'token_unique' => true,
    //系统用户模型
    'token_model' => AdminModel::class,
    //系统用户表
    'system_user_table' => 'system_user',
    //权限模块
    'authModule' => [
        'admin' => '系统模块',
    ],
    //上传filesystem配置中的disk, local本地,qiniu七牛云,oss阿里云
    'uploadDisks' => 'local',

    //地图
    'map'=>[
        'default' => 'amap',
        //高德地图key
        'amap'=>[
          'api_key'=>'7b89e0e32dc5eb583c067edb5491c4d3'
        ],
    ]
];
