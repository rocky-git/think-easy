<?php

namespace thinkEasy\component;

use think\exception\HttpResponseException;
use think\facade\View;

/**
 * 前端组件类
 * Class Component
 * @package thinkEasy\component
 */
class Component
{
    /**
     * 跳转重定向
     * @param string $url 跳转链接
     * @param array $params 参数
     */
    public function redirect($url, array $params = [])
    {
        $query = http_build_query($params);
        if ($query) {
            $url .= '?' . $query;
        }
        throw new HttpResponseException(json([
            'code' => 40021,
            'url' => $url
        ]));
    }

    /**
     * 渲染组件模板
     * @param $template 模板文件名
     * @param array $vars 模板变量
     * @param array $props 组件参数
     * @return string
     * @throws \Exception
     */
    public function view($template,$vars = [],$props = []){
        $view = View::fetch($template,$vars);
        $componentProps = [];
        foreach ($props as $prop=>$value){
            if(is_string($value)){
                $componentProps[] = "{$prop}='{$value}'";
            }elseif (is_array($value)){
                $value = json_encode($value,JSON_UNESCAPED_UNICODE);
                $componentProps[] = ":{$prop}='{$value}'";
            }elseif (is_bool($value)){
                if($value){
                    $componentProps[] = ":{$prop}='true'";
                }else{
                    $componentProps[] = ":{$prop}='false'";
                }
            }
        }
        $componentProps = implode(' ',$componentProps);
        return "<eadmin-component data='" . rawurlencode($view) . "' $componentProps></eadmin-component>";
    }
}
