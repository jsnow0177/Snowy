<?php
namespace Snowy\Helpers;

class Arr{

    /**
     * @param array $arr
     * @return array
     */
    public static function sanitize($arr){
        foreach($arr as $k=>$v){
            if(is_array($v)){
                $arr[$k] = Arr::sanitize($v);
            }else{
                $arr[$k] = Str::sanitize($v);
            }
        }

        return $arr;
    }

}
?>