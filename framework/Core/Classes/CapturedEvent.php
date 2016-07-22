<?php
namespace Snowy\Core\Classes;

/**
 * Class CapturedEvent
 * @package Snowy\Core\Classes
 */
class CapturedEvent{

    /**
     * @var string Имя события
     */
    public $event;

    /**
     * @var string Тип обработчика (фильтр или зацепка)
     */
    public $type;

    /**
     * @var int Количество переданных аргументов
     */
    public $argsCount;

    /**
     * @var array Типы переданных аргументов
     */
    public $args;

    /**
     * @param string $eventName
     * @param string $type
     * @param array $argsTypes
     */
    public function __construct($eventName, $type, $argsTypes = []){
        $this->event = $eventName;
        $this->type = $type;
        $this->argsCount = count($argsTypes);
        $this->args = $argsTypes;
    }

}
?>