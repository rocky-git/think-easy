<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-15
 * Time: 20:28
 */

namespace thinkEasy\model;



class SystemMenu extends BaseModel
{
    public static function onAfterInsert(Model $model)
    {
        cache('eadmin_menu_list', null);
    }

    public static function onAfterUpdate(Model $model)
    {
        cache('eadmin_menu_list', null);
    }

    public static function onAfterDelete(Model $model)
    {
        cache('eadmin_menu_list', null);
    }
}