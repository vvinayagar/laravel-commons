<?php
/**
 * Created by PhpStorm.
 * User: Adekhaha2
 * Date: 2018-06-29
 * Time: 6:55 PM
 */

namespace Commons\Helpers;


class ReflectionHelper
{
    /**
     * The classes must be loaded first before this function can be used.
     * 
     * To pre-load the classes, you can iterate over the folder where the classes resides and do 'require_once $file;'
     * 
     * @param $class
     * @return \Illuminate\Support\Collection
     */
    public static function getAllChildrenOfClass($class)
    {
        $classes = get_declared_classes();
        return collect($classes)->filter(function ($declaredClass) use ($class) {
            return is_subclass_of($declaredClass, $class);
        });
    }
}
