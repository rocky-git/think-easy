<?php


class Manager
{
    //插件目录
    protected $directory;

    public function __construct()
    {
        $this->directory = config('admin.extension.dir','eadmin-plugs');
    }

    public function add($name,$description){

    }
}
