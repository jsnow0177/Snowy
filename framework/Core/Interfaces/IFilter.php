<?php
namespace Snowy\Core\Interfaces;

/**
 * Interface IFilter
 * @package Snowy\Core\Interfaces
 */
interface IFilter{

    /**
     * @param string $eventName Событие, на которое должен быть подписан фильтр
     * @param Callable $handler Обработчик фильтра
     * @param int $priority Приоритет фильтра
     */
    function __construct($eventName, Callable $handler, $priority = FILTER_PRIORITY_LOW);

    /**
     * Возвращает имя события, к которому подключён фильтр
     * @return string
     */
    function getEventName();

    /**
     * Возвращает приоритет фильтра
     * @return int
     */
    function getPriority();

    /**
     * Возвращает ID фильтра
     * @return int
     */
    function getId();

    /**
     * Запускает обработчик фильтра
     * @param ...$args
     * @return bool
     */
    function runHandler(&...$args);

}
?>