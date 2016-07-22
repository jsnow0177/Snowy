<?php
//Точка входа
//Нужно определить путь к этому файлу как WWW_PATH
DEFINE("WWW_PATH", dirname(__FILE__));

/************************************************************/
/*=== НАСТРОЙКИ ДОЛЖНЫ БЫТЬ ПОМЕЩЕНЫ ЗДЕСЬ ===*/

//TODO: Специальные настройки
date_default_timezone_set("Europe/Kiev");
mb_internal_encoding("UTF-8");

/*============== КОНЕЦ  НАСТРОЕК ==============*/
/***********************************************************/

//Теперь нужно подключить файл-инициализатор фреймворка
include_once((WWW_PATH . "/../framework/framework.php"));

$snowy = \Snowy\Core\Snowy::instance();
$snowy->add("/")
    ->add("@/user/#{user_id}", ["controller" => "User", "action" => "Show"])
    ->setArgsTypes([
        "user_id" => "int"
    ])
    ->add("json", ["action" => "json_example"]);

$snowy->captureRequest();
?>