<?php

namespace app\admin\controller;

use thinkEasy\controller\BaseAdmin;
use thinkEasy\form\Form;
use thinkEasy\grid\Actions;
use thinkEasy\grid\Detail;
use thinkEasy\grid\Filter;
use thinkEasy\grid\Grid;
use app\model\SystemSms;

/**
 * 短信记录
 * Class SmsLog
 * @package app\admin\controller
 */
class SmsLog extends BaseAdmin
{
    /**
     * 日志列表
     * @auth true
     * @login true
     * @return string
     */
    protected function grid()
    {
        $grid = new Grid(new SystemSms);
        $grid->indexColumn();
		$grid->userInfo('user.headimg','user.nickname');
		$grid->column('phone','手机号');
		$grid->column('content','短信内容');
		$grid->column('result_type','发送结果')->display(function ($val,$data){
		    if($val == 1){
		        return "<el-tag size='mini' type='success'>成功</el-tag>";
            }else{
                return "<el-tag size='mini' type='info'>{$data['error_msg']}</el-tag>";
            }
        });
		$grid->column('create_time','发送时间');
		$grid->filter(function (Filter $filter){
            $filter->eq('result_type','发送结果')->select([1=>'成功',0=>'失败']);
            $filter->eq('phone','手机号');
            $filter->like('content','短信内容');
            $filter->dateRange('create_time','发送时间');
        });
        $grid->quickSearch();
        $grid->actions(function (Actions $action){
        	 $action->hideDetail();
        	 $action->hideEdit();
        });
        $grid->hideAddButton();
        return $grid;
    }
}
