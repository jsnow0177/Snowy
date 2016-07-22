<?php
namespace Snowy\Helpers;

/**
 * Class Uri
 * @package Snowy\Helpers
 */
class Uri{

    /**
     * Текущий адрес
     * @var null|array
     */
    private static $currentUrl = null;

    /**
     * Возвращает текущий адрес
     * @param int $flag
     * @return string
     */
    public static function getCurrent($flag = URI_RETURN_PATH){
        if(is_null(self::$currentUrl)){
            //Собираем адрес
            self::$currentUrl = [
                "protocol" => "",
                "site" => "",
                "path" => "",
                "q" => [],
                "port" => 80
            ];

            $protocol = (!empty($_SERVER['HTTPS']))?"https":"http";
            $default_port = ($protocol==="https")?443:80;
            $port = "";//($_SERVER['SERVER_PORT'] == $default_port)?"":":".$_SERVER['SERVER_PORT'];
            $site = $_SERVER['HTTP_HOST'];
            $path = self::preparePath($_SERVER['REQUEST_URI']);
            $q = $_GET;

            self::$currentUrl['protocol'] = $protocol;
            self::$currentUrl['site'] = $site . $port;
            self::$currentUrl['path'] = $path;
            self::$currentUrl['q'] = $q;
            self::$currentUrl['port'] = $_SERVER['SERVER_PORT'];
        }

        $url = "";
        if($flag&URI_RETURN_SITE)
            $url .= self::$currentUrl['protocol'] . "://" . self::$currentUrl['site'];
        if($flag&URI_RETURN_PATH)
            $url .= self::$currentUrl['path'];
        if($flag&URI_RETURN_Q) {
            if(count(self::$currentUrl['q']) > 0)
                $url .= "?";
            $_ = [];
            foreach(self::$currentUrl['q'] as $k=>$v){
                $_[] = $k . "=" . $v;
            }
            $url .= implode("&", $_);
        }

        return $url;
    }

    /**
     * @param string $uri
     * @return string
     */
    public static function preparePath($uri){
        $uri = trim(urldecode($uri), "\t\n\r\0\x0B/");
        if(($qPos = mb_strpos($uri, "?")) !== false)
            $uri = mb_substr($uri, 0, $qPos);
        if(mb_strpos($uri, "/") !== 0)
            $uri = "/" . $uri;
        if(mb_strrpos($uri, "/") !== mb_strlen($uri)-1)
            $uri .= "/";

        return $uri;
    }

    /**
     * @param string $path1
     * @param string $path2
     * @return string
     */
    public static function concatPaths($path1, $path2){
        $path = "";
        $path1 = Uri::preparePath($path1);
        $path2 = Uri::preparePath($path2);
        $path = mb_substr($path1, 0, mb_strlen($path1)-1);
        $path .= $path2;
        return $path;
    }

    /**
     * @param string $path
     * @return int
     */
    public static function getLength($path){
        $path = Uri::preparePath($path);
        return count(explode("/", $path));
    }

    /**
     * @return string
     */
    public static function assetsLink(){
        $assets = config()->get("links.assets");
        $linkType = "internal";
        if(preg_match("/^https?:\/\//", $assets))
            $linkType = "external";
        if($linkType==="internal")
            $assets = ((mb_strpos($assets, "/")===0)?$assets:"/".$assets);
        return (($linkType==="external")?$assets:(self::getCurrent(URI_RETURN_SITE) . $assets));
    }

}
?>