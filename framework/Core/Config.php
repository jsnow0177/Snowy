<?php
namespace Snowy\Core;
use Snowy\Core\Classes\Singleton;
use Snowy\Core\Traits\DotAccess;

/**
 * Class Config
 * @package Snowy\Core
 */
class Config extends Singleton{
    use DotAccess {
        _setAccessedProp as private;
        _set as public set;
        _get as public get;
    }

    /**
     * @var array
     */
    protected $storage = [];

    /**
     * Конструктор
     */
    protected function __construct(){
        //Загружаем базовую конфигурацию
        $config = array();
        $basicConfigFile = FW_PATH . "basic_config.php";
        if(file_exists($basicConfigFile)){
            //ob_start();
            $config = include($basicConfigFile);
            //ob_end_clean();
        }
        $appConfigFile = APP_PATH . "config.php";
        if(file_exists($appConfigFile)){
            //ob_start();
            $appConfigArr = include($appConfigFile);
            if(!is_array($appConfigArr))
                $appConfigArr = [];
            //ob_end_clean();
            //Replace arrays
            $config = array_replace_recursive($config, $appConfigArr);
        }
        $this->storage = $config;
        $this->_setAccessedProp("storage");
    }

}
?>