<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-11-08
 * Time: 09:56
 */

namespace thinkEasy\grid\excel;


abstract class AbstractExporter
{
    //文件名
    protected $fileName;
    //表头列
    protected $columns;

    protected $data;

    protected $only = [];

    protected $except = [];
    /**
     * 设置表头列
     * @param array $columns
     * @return $this
     */
    public function columns(array $columns){
        $this->columns = $columns;
        return $this;
    }
    protected function filterColumns(){
        $columns = [];
        $bool = null;
        if(count($this->only) > 0){
            $bool = true;
            $filterColumns = $this->only;
        }elseif (count($this->except) > 0){
            $bool = false;
            $filterColumns = $this->except;
        }
        if($bool !== null){
            foreach ($this->columns as $field=>$title){
                if(in_array($field,$filterColumnst) === $bool){
                    $columns[$field] = $title;
                }
            }
            $this->columns = $columns;
        }
    }
    /**
     * 设置文件名
     * @param $name
     * @return $this
     */
    public function file($name){
        $this->fileName = $name;
        return $this;
    }
    /**
     * 数据源
     * @param $data
     */
    public function rows($data){
        $this->data = $data;
        return $this;
    }
    abstract public function export();
}