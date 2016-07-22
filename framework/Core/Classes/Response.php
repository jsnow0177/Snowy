<?php
namespace Snowy\Core\Classes;

/**
 * Class Response
 * @package Snowy\Core\Classes
 */
final class Response{

    private static $httpTextStatuses = [
        //Success
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        204 => "No Content",
        205 => "Reset Content",
        //Redirection
        301 => "Moved Permanently",
        302 => "Moved Temporarily",
        304 => "Not Modified",
        //Client error
        400 => "Bad Request",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        418 => "I'm a teapot",
        429 => "Too Many Requests",
        //Server error
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout"
    ];

    private static $headersSent = false;
    private static $responseSent = false;

    public $manageContentType = false;
    private $httpStatus = 200;
    private $headers = [];
    private $content = "";
    private $prepared_content = "";

    /**
     * @return bool
     */
    public static function isHeadersSent(){
        return self::$headersSent;
    }

    /**
     * @return bool
     */
    public static function isResponseSent(){
        return self::$responseSent;
    }

    /**
     * @return Cookies
     */
    public function cookies(){
        return Cookies::instance();
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status){
        $this->httpStatus = $status;
        return $this;
    }

    /**
     * Устанавливает заголовок
     * @param string $header
     * @return $this
     */
    public function setHeader($header){
        $headerName = mb_substr($header, 0, mb_strpos($header, ":"));
        $headerContent = trim(mb_substr($header, mb_strlen($headerName)+1), " ");
        if($headerContent === ""){
            if(isset($this->headers[$headerName]))
                unset($this->headers[$headerName]);
            return $this;
        }
        if(!isset($this->headers[$headerName]))
            $this->headers[$headerName] = "";
        $this->headers[$headerName] = $headerContent;

        return $this;
    }

    /**
     * Возвращает HTTP-заголовок ответа
     * @param string $headerName
     * @return bool|string
     */
    public function getHeader($headerName){
        return ((isset($this->headers[$headerName]))?$this->headers[$headerName]:false);
    }

    /**
     * Устанавливает заголовок Content-Type
     * @param string $contentType
     * @param string $charset
     * @return $this
     */
    public function contentType($contentType, $charset = ""){
        $header = "Content-Type: " . $contentType;
        if(mb_strpos($contentType, "text") === 0) {
            $header .= "; charset=" . (($charset === "") ? "utf-8" : $charset);
        }
        return $this->setHeader($header);
    }

    /**
     * Устанавливает заголовок Content-Language
     * @param string|array $contentLanguage
     * @return $this
     */
    public function contentLanguage($contentLanguage){
        $header = "Content-Language: ";
        if(is_array($contentLanguage))
            $contentLanguage = implode(", ", $contentLanguage);
        $header .= $contentLanguage;
        return $this->setHeader($header);
    }

    /**
     * Устанавливает заголовок Content-Length
     * @param bool $reset
     * @return $this
     */
    public function contentLength($reset = false){
        if($reset){
            return $this->setHeader("Content-Length: ");
        }
        $header = "Content-Length: " . mb_strlen($this->prepared_content, "8bit");
        return $this->setHeader($header);
    }

    /**
     * Устанавливает заголовок Location
     * @param string $uri
     * @return Response
     */
    public function location($uri){
        $header = "Location: " . $uri;
        return $this->setHeader($header);
    }

    /**
     * Устанавливает заголовок Cache-Control
     * @param string|array $cacheControl
     * @return $this
     */
    public function cacheControl($cacheControl){
        $header = "Cache-Control: ";
        if(is_array($cacheControl))
            $cacheControl = implode(" ", $cacheControl);
        $header .= $cacheControl;
        return $this->setHeader($header);
    }

    /**
     * Устанавливает заголовок Pragma
     * @param string $pragma
     * @return $this
     */
    public function pragma($pragma){
        $header = "Pragma: " . $pragma;
        return $this->setHeader($header);
    }

    /**
     * Устанавливает данные ответа
     * @param mixed $content
     * @return $this
     */
    public function content($content){
        $this->content = $content;
        //Подготавливаем контент
        $type = gettype($content);
        switch($type) {
            case "array": {
                $this->prepared_content = json_encode($content);
                if(!$this->manageContentType)
                    $this->contentType("application/json");
            } break;
            case "object": {
                if(($content instanceof View)){
                    $this->prepared_content = $content->render();
                    if(!$this->manageContentType)
                        $this->contentType("text/html");
                }else{
                    $this->prepared_content = @json_encode($content);
                    if(!$this->manageContentType)
                        $this->contentType("application/json");
                }
            } break;
            case "boolean":
            case "integer":
            case "double":
            case "null":
            case "resource":
            case "unknown type":
            default: {
                $this->prepared_content = strval($content);
                if(!$this->manageContentType)
                    $this->contentType("text/plain");
            } break;
        }
        return $this;
    }

    /**
     * Отправляет HTTP-заголовки ответа
     * @throws \HttpException
     * @return $this
     */
    public function sendHeaders(){
        //Отправляем статус
        if(!self::$headersSent && !self::$responseSent) {
            header("HTTP/1.1 " . $this->httpStatus . " " . self::$httpTextStatuses[$this->httpStatus]);
            foreach ($this->headers as $headerName => $headerContent) {
                header(($headerName . ": " . $headerContent));
            }
            self::$headersSent = true;
        }else{
            throw new \HttpException("Headers already sent");
        }

        return $this;
    }

    /**
     * Отправляет контент
     */
    public function sendContent(){
        //TODO: !
        if(!self::$responseSent){
            echo $this->prepared_content;
        }
    }

    /**
     * @return mixed
     */
    public function getContent(){
        return $this->content;
    }

    /**
     * Завершает ответ. Отправляет заголовки и тело ответа
     * @throws \HttpException
     */
    public function end(){
        if(!self::$responseSent){
            if(!self::$headersSent){
                $this->cookies()->sendCookies();
                $this->sendHeaders();
            }
            $this->sendContent();
            self::$responseSent = true;
        }
    }

}
?>