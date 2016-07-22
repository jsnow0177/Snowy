<?php
namespace Snowy\Core;

/**
 * Class PrimaryAutoloader
 * @description Базовый автозагрузчки классов
 * @package Snowy\Core
 */
final class PrimaryAutoloader{

    private function __construct(){}
    private function __clone(){}

    /**
     * Функция автозагрузки
     * @param string $class_name Имя класса для загрузки
     */
    public static function load($class_name){
        if(mb_strpos($class_name, "Snowy") === 0){
            $fileName = FW_PATH . mb_substr(str_replace("\\", _DS_, $class_name), 5) . ".php";
            if(file_exists($fileName)){
                require_once($fileName);
            }
        }
    }

}
?>