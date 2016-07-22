<?php
namespace Snowy\Helpers;

/**
 * Class Str
 * @package Snowy\Helpers
 */
class Str{

    /**
     * @param string $string
     * @return string
     */
    public static function sanitize($string){
        return strip_tags(htmlspecialchars($string, ENT_QUOTES));
    }

    /**
     * Делает то же, что и ucfirst но для многобайтных кодировок
     * Используется mb_*
     * @param string $str
     * @return string
     */
    public static function ucfirst($str){
        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }

    /**
     * Трансформирует строку в CamelCase
     * @param $string
     * @param string $delimiter
     * @return string
     */
    public static function toCamelCase($string, $delimiter = "_"){
        $string = self::ucfirst(mb_strtolower($string));
        while(($delPos = mb_strpos($string, $delimiter)) !== false){
            $_str = mb_substr($string, 0, $delPos);
            $_str2 = mb_substr($string, $delPos+1);
            $string = $_str . self::ucfirst($_str2);
        }

        return $string;
    }

    /**
     * @param string $string
     * @param string $delimiter
     * @return string
     */
    public static function ucAfter($string, $delimiter = "\\"){
        $string = self::ucfirst($string);
        $startPos = 0;
        while(($delPos = mb_strpos($string, $delimiter, $startPos)) !== false){
            $startPos = $delPos+1;
            $_str = mb_substr($string, 0, $delPos+1);
            $_str2 = mb_substr($string, $delPos+1);
            $string = $_str . self::ucfirst($_str2);
        }

        return $string;
    }

}
?>