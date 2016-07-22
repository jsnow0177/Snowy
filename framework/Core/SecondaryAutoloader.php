<?php
namespace Snowy\Core;

/**
 * Class SecondaryAutoloader
 * @package Snowy\Core
 */
class SecondaryAutoloader{

    /**
     * @param string $class_name
     */
    public static function load($class_name){

        $cfg = Config::instance();
        $app_namespace_prefix = $cfg->get("app.namespace_prefix", "");
        $classFounded = false;
        if($app_namespace_prefix !== "" && mb_strpos($class_name, $app_namespace_prefix) === 0){
            $classFile = APP_PATH . mb_substr(str_replace("\\", _DS_, $class_name), mb_strlen($app_namespace_prefix)+1) . ".php";
            if(file_exists($classFile)){
                $classFounded = true;
                require_once($classFile);
            }
        }

        if(!$classFounded)
            hooks_apply("autoload_class", $class_name);
    }

}
?>