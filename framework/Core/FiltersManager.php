<?php
namespace Snowy\Core;
use Snowy\Core\Interfaces\IFilter;
use Snowy\Helpers\FS;

/**
 * Class FiltersManager
 * Менеджер фильтров
 * @package Snowy\Core
 */
final class FiltersManager{

    /**
     * @var array Коллекция объектов фильтров
     */
    private static $filtersCollection = [];

    /**
     * @var array Последовательность приоритетов
     */
    private static $prioritiesSequenceCollection = [];

    /**
     * @var array
     */
    private static $emptySpaces = [];

    /**
     * @var int Количество зарегистрированных фильтров
     */
    private static $filtersCount = 0;

    /**
     * @var bool
     */
    private static $isLoaded = false;

    /**
     * Загружает фильтры
     */
    public static function loadFilters(){
        if(!self::$isLoaded){
            //Загружаем фильтры
            //Загружаем системные фильтры
            $sysFiltersPath = FW_PATH . "Filters" . _DS_;
            if(is_dir($sysFiltersPath)){
                $filters = FS::getFilesRecursive($sysFiltersPath);
                foreach($filters as $filterFile){
                    include_once($filterFile);
                }
            }
            //Загружаем фильтры приложения
            $appFiltersPath = APP_PATH . "Filters" . _DS_;
            if(is_dir($appFiltersPath)){
                $filters = FS::getFilesRecursive($appFiltersPath);
                foreach($filters as $filterFile){
                    include_once($filterFile);
                }
            }

            self::$isLoaded = true;
            hooks_apply("filters_loaded", self::getCount());
        }
    }

    /**
     * Возвращает количество зарегистрированных фильтров
     * @return int
     */
    public static function getCount(){
        return self::$filtersCount;
    }

    /**
     * Регистрирует фильтр
     * @param IFilter $filter
     * @return bool
     */
    public static function registerFilter(IFilter $filter){
        $filterId = $filter->getId();
        $filterPriority = $filter->getPriority();

        if(isset(self::$prioritiesSequenceCollection[$filterPriority])){
            foreach(self::$filtersCollection[$filterPriority] as $registeredFilter){
                $rFilterId = $registeredFilter->getId();
                if($filterId === $rFilterId){
                    return false;
                }
            }
        }

        //Не нашли ни в одном из разделов такой же фильтр. Можно регистрировать
        if(!isset(self::$filtersCollection[$filterPriority])) {
            self::$filtersCollection[$filterPriority] = [];
            self::$emptySpaces[$filterPriority] = 0;
            self::$prioritiesSequenceCollection[] = $filterPriority;
            self::$filtersCollection[$filterPriority][] = $filter;
            rsort(self::$prioritiesSequenceCollection, SORT_NUMERIC);
        }else{
            //Если такой приоритет существует, то выполняем поиск пустых мест
            if(self::$emptySpaces[$filterPriority] > 0){
                foreach(self::$filtersCollection[$filterPriority] as $k=>$v){
                    if(is_null($v)){
                        self::$filtersCollection[$filterPriority][$k] = $filter;
                        self::$emptySpaces[$filterPriority]--;
                        break;
                    }
                }
            }else{
                self::$filtersCollection[$filterPriority][] = $filter;
            }
        }
        self::$filtersCount++;

        return true;
    }

    /**
     * Удаляет фильтр
     * @param IFilter $filter
     * @return bool
     */
    public static function unregisterFilter(IFilter $filter){
        $filterId = $filter->getId();
        $filterPriority = $filter->getPriority();

        if(isset(self::$filtersCollection[$filterPriority])){
            foreach(self::$filtersCollection[$filterPriority] as $k=>$registeredFilter){
                $rFilterId = $registeredFilter->getId();
                if($filterId === $rFilterId){
                    self::$filtersCollection[$filterPriority][$k] = null;
                    self::$emptySpaces[$filterPriority]++;
                    self::$filtersCount--;
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Запускает выполнение события
     * @param string $eventName
     * @param ...$args
     */
    public static function runAction($eventName, &...$args){
        $eventName = mb_strtolower($eventName);
        $types = [];
        foreach($args as $arg){
            $type = gettype($arg);
            if($type === "object")
                $type = get_class($arg);
            $types[] = $type;
        }
        Snowy::captureEvent($eventName, "filter", $types);
        foreach(self::$prioritiesSequenceCollection as $priority){
            foreach(self::$filtersCollection[$priority] as $filter){
                if(!is_null($filter) && $filter->getEventName() === $eventName){
                    $filterExecutionResult = call_user_func_array(array($filter, "runHandler"), $args);
                    if(!$filterExecutionResult)
                        return;
                }
            }
        }
    }

}
?>