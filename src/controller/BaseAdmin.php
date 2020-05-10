<?php
declare (strict_types=1);

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
        $this->successCode($this->form()->addExtraData(['submitFromMethod' => 'form'])->view());
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $submitFromMethod = $request->post('submitFromMethod');
        $res = $this->$submitFromMethod()->save($request->post());
        if ($res!==false) {
            $this->successCode([], 200, '数据保存成功');
        } else {
            $this->errorCode(999, '数据保存失败');
        }

    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //

    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $this->successCode($this->form()->addExtraData(['submitFromMethod' => 'form'])->edit($id)->view());
    }
   
    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        if($id == 'batch'){
            $ids = $request->put('ids');
            $res = $this->grid()->update($ids, $request->put());
        }else{
            $submitFromMethod = $request->put('submitFromMethod');
            $res = $this->$submitFromMethod()->update($id, $request->put()); 
        }
        if($res !== false){
            $this->successCode([], 200, '数据更新成功');
        }else {
            $this->errorCode(999, '数据保存失败');
        }

    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $res = $this->grid()->destroy($id);
        if ($res !== false) {
            $this->successCode([], 200, '删除成功');
        } else {
            $this->errorCode(999, '删除失败');
        }

    }

    public function view($build)
    {
        if(request()->method() == 'GET' ){
            $this->successCode($build->view());
        }else{
            return $build;
        }
    }
}
