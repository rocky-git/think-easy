<?php


namespace thinkEasy\layout;


use thinkEasy\View;

class Row extends View
{
    protected $html = '';
    protected $gutter = 0;
    protected $component = [];
    /**
     * 添加列
     * @param $content 内容
     * @param $span 栅格占据的列数,占满一行24,默认24
     */
    public function column($content,$span = 24){
        $column = new Column();
        $column->span($span);
        if($content instanceof \Closure){
            call_user_func($content,$column);
        }else{
            $column->content($content);
        }
        $this->html .= $column->render();
        $this->component = array_merge($this->component,$column->getComponents());
    }
    /**
     * 添加列组件
     * @param $component 组件
     * @param $span 栅格占据的列数,占满一行24,默认24
     */
    public function columnComponent($component,$span = 24){
        $column = new Column();
        $column->span($span);
        $componentKey = 'component'.mt_rand(10000,99999);
        $this->component[$componentKey] = "() => new Promise(resolve => {
                            resolve(this.\$splitCode(decodeURIComponent('".rawurlencode($component)."')))
                        })";
        $column->content('<component :is="'.$componentKey.'" />');
        $this->html .= $column->render();
    }
    /**
     * 添加列组件
     * @param $url 组件
     * @param $span 栅格占据的列数,占满一行24,默认24
     */
    public function columnComponentUrl($url,$span = 24){
        $column = new Column();
        $column->span($span);
        $componentKey = 'component'.mt_rand(10000,99999);
        $component = "<template><div></div></template>";
        $this->component[$componentKey] = "() => new Promise(resolve => {
                            resolve(this.\$splitCode(decodeURIComponent('".rawurlencode($component)."')))
                            this.\$request({
                                url: '{$url}',
                            }).then(res=>{
                                    this.{$componentKey} = () => new Promise(resolve => {
                                        resolve(this.\$splitCode(res.data))
                                    })
                            })
                        })";
        $column->content('<component :is="'.$componentKey.'" />');
        $this->html .= $column->render();
    }
    public function getComponents(){
        return $this->component;
    }
    /**
     * flex布局
     * @param $justify flex 布局下的水平排列方式
     * @param $align flex 布局下的垂直排列方式
     */
    public function flex($justify,$align){
        $this->setAttr(':type','flex');
        $this->setAttr(':justify',$justify);
        $this->setAttr(':align',$align);
    }
    /**
     * 设置栅格间隔
     * @param $number
     */
    public function gutter($number){
        $this->setAttr(':gutter',$number);
    }
    public function render(){
        list($attrStr, $scriptVar) = $this->parseAttr();
        return "<el-row style=\"margin-bottom: 15px;\" $attrStr>{$this->html}</el-row>";
    }
}
