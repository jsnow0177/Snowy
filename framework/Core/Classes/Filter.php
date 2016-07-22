<?php
namespace Snowy\Core\Classes;
use Snowy\Core\Interfaces\IFilter;

/**
 * Class Filter
 * @package Snowy\Core\Classes
 */
class Filter implements IFilter{

    /**
     * @var int
     */
    private static $nextId = 1;

    /**
     * @var int
     */
    private $filterId;

    /**
     * @var string
     */
    protected $eventName;

    /**
     * @var Callable
     */
    protected $handler;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @return int
     */
    protected static function getNextId(){
        return self::$nextId++;
    }

    /**
     * @param string $eventName Событие, на которое должен быть подписан фильтр
     * @param Callable $handler Обработчик фильтра
     * @param int $priority Приоритет фильтра
     */
    public function __construct($eventName, Callable $handler, $priority = FILTER_PRIORITY_LOW)
    {
        $this->filterId = self::getNextId();
        $this->eventName = mb_strtolower($eventName);
        $this->handler = $handler;
        $this->priority = $priority;
    }

    /**
     * Возвращает имя события, к которому подключён фильтр
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * Возвращает приоритет фильтра
     * @return int
     */
    function getPriority()
    {
        return $this->priority;
    }

    /**
     * Возвращает ID фильтра
     * @return int
     */
    public function getId()
    {
        return $this->filterId;
    }

    /**
     * Запускает обработчик фильтра
     * @param ...$args
     * @return bool
     */
    public function runHandler(&...$args)
    {
        $executionResult = call_user_func_array($this->handler, $args);
        if(!is_bool($executionResult))
            $executionResult = true;
        return $executionResult;
    }

}
?>