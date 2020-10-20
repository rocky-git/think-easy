<?php
declare (strict_types=1);

namespace thinkEasy\controller;

use think\Request;
use thinkEasy\Controller;
use thinkEasy\facade\Component;

class BaseAdmin extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        Component::view($this->grid()->view());
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        Component::view($this->form()->addExtraData(['submitFromMethod' => 'form'])->view());
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $submitFromMethod = $request->post('submitFromMethod');
        $form = $this->$submitFromMethod();
        $res = $form->save($request->post());
        if ($res !== false) {
            Component::notification()->success('操作完成','数据保存成功',$form->getRedirectUrl());
        } else {
            Component::message()->error('数据保存失败');
        }


    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        Component::view($this->detail()->detailData($id)->view());

    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        Component::view($this->form()->addExtraData(['submitFromMethod' => 'form'])->view());
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $url = '';
        if ($id == 'batch') {
            $ids = $request->put('ids');
            $res = $this->grid()->update($ids, $request->put());
        } else {
            $submitFromMethod = $request->put('submitFromMethod');
            $form = $this->$submitFromMethod();
            $res = $form->update($id, $request->put());
            $url = $form->getRedirectUrl();
        }
        if ($res !== false) {
            Component::notification()->success('操作完成','数据更新成功',$url);
        } else {
            Component::message()->error('数据保存失败');
        }

    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $res = $this->grid()->destroy($id);
        if ($res !== false) {
            Component::notification()->success('操作完成','删除成功');
        } else {
            Component::message()->error('数据保存失败');
        }

    }

    public function view($build)
    {
        if (request()->method() == 'GET') {
            Component::view($build->view());
        } else {
            return $build;
        }
    }
}
