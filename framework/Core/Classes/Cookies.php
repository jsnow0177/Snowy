<?php
namespace Snowy\Core\Classes;

/**
 * Class Cookies
 * @package Snowy\Core\Classes
 */
final class Cookies extends Singleton{

    private $_cookies;

    protected function __construct(){
        $this->_cookies = [];
        foreach($_COOKIE as $k=>$v){
            $this->_cookies[$k] = [[$v, 0, "", "", false, false], false];
        }
    }

    /**
     * @param string $key
     * @param string|int|bool $value
     * @param int $expire
     * @param string|null $path
     * @param string|null $domain
     * @param bool|false $secure
     * @param bool|false $httponly
     */
    public function set($key, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false){
        $this->_cookies[$key] = [[$value, $expire, $path, $domain, $secure, $httponly], true];
    }

    /**
     * Возвращает значение печеньки или null
     * @param string $key
     * @return null|string|int|bool
     */
    public function get($key){
        if(isset($this->_cookies[$key]))
            return $this->_cookies[$key][0][0];
        return null;
    }

    /**
     * Отправляет куки браузеру. Не рекомендуется трогать
     */
    public function sendCookies(){
        if(!Response::isHeadersSent() && !Response::isResponseSent()){
            foreach($this->_cookies as $key => $cookie){
                if($cookie[1] === true){
                    setcookie($key, $cookie[0][0], $cookie[0][1], $cookie[0][2], $cookie[0][3], $cookie[0][4], $cookie[0][5]);
                }
            }
        }
    }

}
?>