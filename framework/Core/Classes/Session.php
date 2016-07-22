<?php
namespace Snowy\Core\Classes;

/**
 * Class Session
 * @package Snowy\Core\Classes
 */
class Session extends Singleton{

    /**
     * @var array
     */
    private $_session;

    /**
     * @var bool
     */
    private $started = false;

    /**
     *
     */
    protected function __construct(){
        $this->_session = [];
        foreach($_SESSION as $k=>$v){
            $this->_session[$k] = $v;
        }
        $self = $this;
    }

    /**
     *
     */
    public function start(){
        if(!$this->started){
            @session_start();
            $this->started = true;
        }
    }

    /**
     * @param $key
     * @return null|string|bool|int|float
     */
    public function get($key){
        if(isset($this->_session[$key]))
            return $this->_session[$key];
        return null;
    }

    /**
     * @param string $key
     * @param string|int|bool|float $val
     */
    public function set($key, $val){
        if($this->started) {
            $this->_session[$key] = $val;
            $_SESSION[$key] = $val;
        }
    }

}
?>