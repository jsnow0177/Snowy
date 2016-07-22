<?php
namespace Snowy\Core\Classes;

/**
 * Class Request
 * @package Snowy\Core\Classes
 */
final class Request{

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $controller;

    /**
     * @var string
     */
    private $action;

    /**
     * @var int IP-адрес последнего в цепочке хоста
     */
    private $ip;

    /**
     * @var string Путь запроса
     */
    private $path;

    /**
     * @var int Тип запроса
     */
    private $requestType;

    /**
     * @var SimpleStorage Массив данных GET
     */
    private $get;

    /**
     * @var SimpleStorage Массив данных POST
     */
    private $post;

    /**
     * @var SimpleStorage Массив данных FILES
     */
    private $files;

    /**
     * @var array|SimpleStorage Массив дополнительных данных
     */
    private $args;

    private $_storage_ = [];

    public function __construct(){
        $this->namespace = "";
        $this->controller = "";
        $this->action = "";
        $this->ip = "";
        $this->path = "";
        $this->requestType = REQ_TYPE_GET;
        $this->get = new SimpleStorage();
        $this->post = new SimpleStorage();
        $this->files = new SimpleStorage();
        $this->args = new SimpleStorage();
    }

    public function __set($name, $value){
        $storages = ["get", "post", "files", "args"];
        if(property_exists($this, $name)){
            if(in_array($name, $storages)){
                if(is_array($value))
                    $value = new SimpleStorage($value);
                if(!($value instanceof SimpleStorage))
                    $value = new SimpleStorage();
                $this->$name = $value;
            }else{
                $this->$name = $value;
            }
        }else{
            $this->_storage_[$name] = $value;
        }
    }

    public function __get($name){
        if(property_exists($this, $name)) {
            return $this->$name;
        }elseif(isset($this->_storage_[$name])){
            return $this->_storage_[$name];
        }

        return false;
    }

}
?>