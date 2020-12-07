<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-09
 * Time: 23:18
 */
use think\facade\Route;

Route::get('/', function () {
    return view('/index');
});

Route::resource(':controller',':controller')->ext('rest');

