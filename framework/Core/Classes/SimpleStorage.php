<?php
namespace Snowy\Core\Classes;
use Snowy\Core\Traits\DotAccess;

/**
 * Class SimpleStorage
 * @package Snowy\Core\Classes
 */
class SimpleStorage{
    use DotAccess{
        _setAccessedProp as private;
        _set as public set;
    }

    protected $storage;

    /**
     * @param array $initial_data
     */
    public function __construct($initial_data = []){
        $this->storage = $initial_data;
        $this->_setAccessedProp("storage");
    }

    /**
     * @param string $key
     * @param false|mixed $default
     * @param bool|true|Callable $sanitize
     * @return mixed
     */
    public function get($key, $default = false, $sanitize = false){
        $result = $this->_get($key, $default);
        if(is_string($result)){
            if(is_bool($sanitize)){
                if($sanitize)
                    $result = htmlspecialchars($result, ENT_QUOTES);
            }elseif(is_callable($sanitize)){
                $result = $sanitize($result);
            }
        }

        return $result;
    }

}
?>