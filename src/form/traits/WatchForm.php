<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-10-06
 * Time: 10:26
 */
namespace thinkEasy\form\traits;
use think\facade\Request;
use thinkEasy\ApiJson;

/**
 * form数据监听
 * Trait Watch
 * @package thinkEasy\form\traits
 */
trait WatchForm
{
    use ApiJson;
    protected $watchs = [];
    protected $watchJs = '';
    /**
     * 设置监听数据方法
     * @param array $data
     */
    public function watch(array $data){
        $this->watchs = $data;
    }
    /**
     * 生成监听js
     * @return string
     */
    protected function createWatchJs(){
        $fields = array_keys($this->watchs);
        $submitUrl = app('http')->getName() . '/' . request()->controller();
        $submitUrl = str_replace('.rest', '', $submitUrl);
        $submitFromMethod = request()->action();
        foreach ($fields as $field){
            $this->watchJs .= <<<EOF
    'form.{$field}': {
         handler: function(newVal,oldValue) {
            let method,url = '{$submitUrl}'
            if(this.form.id == undefined){
                url = url+'.rest'
                method = 'post'
            }else{
                url = url +'/'+this.form.id+'.rest'
                method = 'put'
            }
            this.\$request({
                  url:url,
                  method:method,
                  data:{
                      field:'{$field}',
                      submitFromMethod:'{$this->extraData['submitFromMethod']}',
                      newVal:newVal,
                      oldValue:oldValue,
                      form:this.form,
                      eadmin_form_watch:true
                  }
            }).then(res=>{
                for(field in res.data){
                    if(field == '{$field}' && res.data[field] != newVal){
                       this.form[field] = res.data[field]
                    }else if(field != '{$field}'){
                       this.form[field] = res.data[field]
                    }
                }
            })
         }
     },
EOF;
        }
        return $this->watchJs;
    }
    public function setWatchData($field,$value){
        $this->watchData[$field] = $value;
    }
    /**
     * 监听数据回调
     */
    protected function watchCall(){
        if(Request::has('eadmin_form_watch')){
            $data = Request::post();
            $watch = new \thinkEasy\form\Watch($data['form']);
            $closure = $this->watchs[$data['field']];
            call_user_func_array($closure,[$data['newVal'],$watch,$data['oldValue']]);
            $this->successCode($watch->get());
        }
    }
}