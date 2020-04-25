<?php
declare (strict_types = 1);

namespace thinkEasy\controller;

use think\Request;
use thinkEasy\Controller;

class BaseAdmin extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->successCode($this->grid()->view());
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $this->successCode($this->form()->view());
    }
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $res = $this->form()->save($request->post());
        if($res){
            $this->successCode([],200,'数据保存成功');
        }else{
            $this->errorCode(999,'数据保存失败');
        }

    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
       
    }
    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $this->successCode($this->form()->edit($id)->view());
    }
    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $this->successCode($this->form()->update($id,$request->put()),200,'数据更新成功');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $res = $this->grid()->destroy($id);
        if($res){
            $this->successCode([],200,'删除成功');
        }else{
            $this->errorCode(999,'删除失败');
        }

    }
}
