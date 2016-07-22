<?php
namespace Snowy\Core\Classes;

/**
 * Class Model
 * @package Snowy\Core\Classes
 */
class Model extends Singleton{

    /**
     * @var string Имя модели
     */
    protected $name;

    /**
     * Конструктор
     */
    protected function __construct(){
        $this->name = get_called_class();
    }

}
?>