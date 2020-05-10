<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-29
 * Time: 21:52
 */

namespace thinkEasy\form;


use thinkEasy\View;

class Button extends View
{
    
    /**
     * Button constructor.
     * @param $text 按钮文字
     * @param string $type 颜色类型 primary / success / warning / danger / info / text
     * @param string $size 尺寸 medium / small / mini
     * @param string $icon 图标
     */
    public function create($text='',$type='',$size='small',$icon='',$plain=false)
    {
        $button = new self();
        $button->template  = 'button';
        $button->text  = $text;
        $button->setAttr('type',$type);
        $button->setAttr('size',$size);
        $button->setAttr('icon',$icon);
        $button->setAttr('text',$text);
        if($plain){
            $button->setAttr('plain','true');
        }
        return $button;
    }
    //禁用状态
    public function disabled(){
        $this->setAttr('disabled','true');
        return $this;
    }
    //圆形按钮
    public function circle(){
        $this->setAttr('circle','true');
        return $this;
    }
    //圆角按钮
    public function round(){
        $this->setAttr('round','true');
        return $this;
    }
    /**
     * 打开窗口 modal弹窗对话框 open当前窗口
     * @Author: rocky
     * 2019/9/11 10:02
     * @param $url 跳转链接
     * @param string $type 跳转类型
     */
    public function href($url,$type='open'){
        $this->setAttr('url',$url);
        $this->setAttr('open-type',$type);
        return $this;
    }
    /**
     * 更新数据
     * @Author: rocky
     * 2019/9/11 10:06
     * @param $id 更新主键条件
     * @param array $updateData 更新数据
     * @param string $url
     * @param $confirm 操作提示
     */
    public function save($id,array $data,$url='',$confirm=''){
        $this->setAttr('pk-id',$id);
        $this->setAttr('update-data',json_encode($data,JSON_UNESCAPED_UNICODE));
        $this->setAttr('open-type','update');
        $this->setAttr('url',$url);
        $this->setAttr('confirm',$confirm);
        $this->setAttr(':tabledata.sync','tableData');
        return $this;
    }

    /**
     * 批量更新数据
     * @Author: rocky
     * 2019/9/11 10:06
     * @param $id 更新主键条件
     * @param array $updateData 更新数据
     * @param string $url
     * @param string $confirm 操作提示
     */
    public function saveAll(array $data,$url,$confirm=''){
        $this->setAttr('update-data',json_encode($data,JSON_UNESCAPED_UNICODE));
        $this->setAttr('open-type','updateBatch');
        $this->setAttr('url',$url);
        $this->setAttr('confirm',$confirm);
        $this->setAttr(':tabledata.sync','tableData');
        $this->setAttr(':selectionData','selectionData');
        return $this;
    }
    /**
     * 删除数据
     * @param $id 更新主键条件
     * @param string $confirm 操作提示
     * @param integer $mode 删除模式：0正常删除，1永久删除，2恢复数据（回收站）
     * @return $this
     */
    public function delete($id,$confirm='',$mode=0){
        $this->setAttr('pk-id',$id);
        $this->setAttr('open-type','delete');
        $this->setAttr('mode',$mode);
        $this->setAttr('confirm',$confirm);
        $this->setAttr(':tabledata.sync','tableData');
        return $this;
    }
    /**
     * 返回html
     * @return string
     */
    public function render(){
        list($attrStr, $scriptVar) = $this->parseAttr();
        $html = "<eadmin-button {$attrStr}></eadmin-button>";
        return $html;
    }
}