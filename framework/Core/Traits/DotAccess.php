<?php
namespace Snowy\Core\Traits;

/**
 * Class DotAccess
 * @package Snowy\Core\Traits
 */
trait DotAccess{

    /**
     * @var string
     */
    protected $__accessed_property_name__;

    /**
     * @param string $prop_name
     */
    protected function _setAccessedProp($prop_name){
        $this->__accessed_property_name__ = $prop_name;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function _set($key, $value){
        $keys = explode(".", $key);
        $keys_count = count($keys);

        if($keys_count === 0) return;

        $prop = $this->__accessed_property_name__;
        $array = &$this->$prop;

        for($i = 0; $i < $keys_count; $i++){
            if(!array_key_exists($keys[$i], $array) || !is_array($array[$keys[$i]])) {
                $array[$keys[$i]] = array();
            }

            if($i < $keys_count-1)
                $array = &$array[$keys[$i]];
        }

        $array[$keys[$keys_count-1]] = $value;
    }

    /**
     * @param string $key
     * @param mixed|bool|false $default
     * @return mixed
     */
    protected function _get($key, $default = false){
        $keys = explode(".", $key);
        $keys_count = count($keys);

        if($keys_count === 0) return $default;

        $prop = $this->__accessed_property_name__;
        $array = &$this->$prop;

        for($i = 0; $i < $keys_count; $i++){
            if(!array_key_exists($keys[$i], $array) || ($i < $keys_count-1 && !is_array($array[$keys[$i]]))) {
                return $default;
            }

            $array = &$array[$keys[$i]];
        }

        return $array;
    }

}
?>