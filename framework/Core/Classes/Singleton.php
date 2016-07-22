<?php
namespace Snowy\Core\Classes;

/**
 * Class Singleton
 * @package Snowy\Core\Classes
 */
class Singleton{

    /**
     * @var array
     */
    private static $_instances = [];

    protected function __construct(){}
    protected function __clone(){}

    /**
     * @return $this
     */
    public static function instance(){
        $cls = get_called_class();
        if(is_null(self::$_instances[$cls]))
            self::$_instances[$cls] = (class_exists($cls))?(new $cls):null;
        return self::$_instances[$cls];
    }

}
?>