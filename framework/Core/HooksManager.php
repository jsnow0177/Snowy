<?php
namespace Snowy\Core;
use Snowy\Core\Interfaces\IHook;
use Snowy\Helpers\FS;

/**
 * Class HooksManager
 * Менеджер зацепок
 * @package Snowy\Core
 */
final class HooksManager{

    /**
     * @var array Все зацепки
     */
    private static $hooksCollection = [];

    /**
     * @var int Пустые места (места удалённых ранее зацепок) в коллекции зацепок
     */
    private static $Empty_Spaces = 0; //What are we living for? Abandoned places... I guess we know the score
    //On and on! Does anybody know what we are looking for?

    /**
     * @var int Количество зарегистрированных зацепок
     */
    private static $hooksCount = 0;

    /**
     * @var bool
     */
    private static $isLoaded = false;

    /**
     * Загружает хуки
     */
    public static function loadHooks(){
        if(!self::$isLoaded){
            //Загружаем зацепки системы, а затем приложения
            $systemHooksPath = FW_PATH . "Hooks" . _DS_;
            if(is_dir($systemHooksPath)){
                //Получаем список файлов зацепок
                $hooksList = FS::getFilesRecursive($systemHooksPath);
                foreach($hooksList as $hookFile){
                    include_once($hookFile);
                }
            }

            //Загружаем зацепки приложения
            $appHooksPath = APP_PATH . "Hooks" . _DS_;
            if(is_dir($appHooksPath)){
                //Получаем список файлов зацепок
                $hooksList = FS::getFilesRecursive($appHooksPath);
                foreach($hooksList as $hookFile){
                    include_once($hookFile);
                }
            }

            self::$isLoaded = true;
            self::runAction("hooks_loaded", self::getCount());
        }
    }

    /**
     * Регистрирует зацепку
     * @param IHook $hook
     * @return bool
     */
    public static function registerHook(IHook $hook){
        //Сначала выполняем поиск хука
        $hookId = $hook->getId();
        foreach(self::$hooksCollection as $registeredHook){
            $rHookId = $registeredHook->getId();
            if($rHookId === $hookId){
                return false;
            }
        }

        if(self::$Empty_Spaces > 0){
            foreach(self::$hooksCollection as $space=>$val){
                if(is_null($val)){
                    self::$hooksCollection[$space] = $hook;
                    self::$Empty_Spaces--;
                    break;
                }
            }
        }else {
            self::$hooksCollection[] = $hook;
        }
        self::$hooksCount++;
        return true;
    }

    /**
     * Убирает зацепку
     * @param IHook $hook
     * @return bool
     */
    public static function unregisterHook(IHook $hook){
        $hookId = $hook->getId();
        foreach(self::$hooksCollection as $k=>$registeredHook){
            $rHookId = $registeredHook->getId();
            if($hookId === $rHookId){
                self::$hooksCollection[$k] = null;
                self::$Empty_Spaces++;
                self::$hooksCount--;
                return true;
            }
        }

        return false;
    }

    /**
     * Возвращает количество зарегистрированных зацепок
     * @return int
     */
    public static function getCount(){
        return self::$hooksCount;
    }

    /**
     * Запускает действие, на которое можно повесить зацепки
     * @param string $actionName
     * @param ...$args
     */
    public static function runAction($actionName, ...$args){
        $actionName = mb_strtolower($actionName);
        $types = [];
        foreach($args as $arg){
            $type = gettype($arg);
            if($type === "object")
                $type = get_class($arg);
            $types[] = $type;
        }
        Snowy::captureEvent($actionName, "hook", $types);
        foreach(self::$hooksCollection as $hook){
            if(!is_null($hook) && $hook->getAction() === $actionName){
                $hookExecutionResult = call_user_func_array(array($hook, "runHandler"), $args);
                if(!$hookExecutionResult)
                    break;
            }
        }
    }

}
?>