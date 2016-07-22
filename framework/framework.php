<?php
if(!defined("WWW_PATH"))
    throw new ErrorException("Не определён путь к директории приложения WWW_PATH");

/**
 * Directory separator
 */
DEFINE("_DS_", DIRECTORY_SEPARATOR);
/**
 * Путь к директории с приложением
 */
DEFINE("APP_PATH", WWW_PATH . _DS_);
/**
 * Путь к директории с фреймворком
 */
DEFINE("FW_PATH", dirname(__FILE__) . _DS_);

// Подключаем первичный автозагрузчик
$_primaryAutoloaderFile = FW_PATH . implode(_DS_, ["Core", "PrimaryAutoloader.php"]);
require_once($_primaryAutoloaderFile);
spl_autoload_register(array("Snowy\\Core\\PrimaryAutoloader", "load"));

//Подключаем глобальные константы
$_globalConstantsFile = FW_PATH . "constants.php";
require_once($_globalConstantsFile);

//Подключаем глобальные функции
$_globalFXFile = FW_PATH . "functions.php";
require_once($_globalFXFile);

//Включаем запись событий
\Snowy\Core\Snowy::captureEvents(true);

//Загружаем хуки и фильтры
\Snowy\Core\HooksManager::loadHooks();
\Snowy\Core\FiltersManager::loadFilters();

//Загружаем конфигурацию
$config = \Snowy\Core\Config::instance();

//Настраиваем приложение
\Snowy\Core\Snowy::captureEvents($config->get("app.capture_events"));

//Регистрируем вторичный автозагрузчик классов
spl_autoload_register(array("Snowy\\Core\\SecondaryAutoloader", "load"));

//Запускаем сессию, если включен автостарт
if($config->get("app.session.autostart", false))
    \Snowy\Core\Classes\Session::instance()->start();

//Инстанцируем объект приложения
$snowy = \Snowy\Core\Snowy::instance();

// Сообщаем, что фреймворк инициализирован
hooks_apply("init", $snowy);
?>