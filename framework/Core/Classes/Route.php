<?php
namespace Snowy\Core\Classes;
use Snowy\Core\Config;
use Snowy\Core\Interfaces\IRoute;
use Snowy\Core\Snowy;
use Snowy\Helpers\Uri;

/**
 * Class Route
 * @package Snowy\Core\Classes
 */
final class Route implements IRoute{

    /**
     * @var string Базовый маршрут
     */
    private $basePath;

    /**
     * @var string Маршрут
     */
    private $path;

    /**
     * @var bool
     */
    private $isPattern;

    /**
     * @var array
     */
    private $uriArgs;

    /**
     * @var int
     */
    private $uriArgsCount;

    /**
     * @var array
     */
    private $additionalArgs;

    /**
     * @var int
     */
    private $requestType;

    /**
     * @var Callable|null
     */
    private $handler;

    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * Подготавливает маршрут
     * @param string $path
     * @return array
     */
    private static function preparePath($path){
        $isPattern = false;
        $args = [];
        $count = 0;
        $path = Uri::preparePath($path);
        $path = mb_substr($path, 1);
        if($path === "") $path = "/";
        if(($atPos = mb_strpos($path, "@")) === 0){
            $path = Uri::preparePath(mb_substr($path, 1));
            $isPattern = true;
            $path = preg_quote($path, "/");
            //Маршрут обычно выглядит примерно так:
            // user/#{id}/show
            // api/#{group}.#{method}
            // api/#{namespace}/#{group}.#{method}/#{token}
            $path = "/^" . preg_replace_callback("/\#\\\{([\w\d\-]+)\\\}/", function($m) use (&$args){
                $args[] = $m[1];
                return "(?<" . $m[1] . ">[\w\d\-\+]+)";
            }, $path, -1, $count) . "$/";
        }else{
            $path = Uri::preparePath($path);
        }

        return [$path, $isPattern, $args, $count];
    }

    /**
     * @param string $basePath Базовый маршрут (маршрут, на котором "висит" приложение)
     * @param string $path
     * @param array $additionalArgs
     * @param Callable|null $handler
     */
    public function __construct($basePath, $path, $additionalArgs = [], $handler = null)
    {
        $this->basePath = Uri::preparePath($basePath);
        $this->additionalArgs = $additionalArgs;
        $this->requestType = REQ_TYPE_ALL;
        $pathInfo = self::preparePath($path);
        $this->path = $pathInfo[0];
        $this->isPattern = $pathInfo[1];
        $this->uriArgs = [];
        foreach($pathInfo[2] as $arg){
            $this->uriArgs[$arg] = "string";
        }
        $this->uriArgsCount = $pathInfo[3];
        $this->handler = $handler;
    }

    /**
     * Устанавливает типы для URI-аргументов
     * @param array $types
     * @return $this
     */
    public function setArgsTypes($types = [])
    {
        if(!is_array($types))
            throw new \InvalidArgumentException("\$types must be an array");
        foreach($types as $k=>$v){
            if(isset($this->uriArgs[$k])){
                $this->uriArgs[$k] = $v;
            }
        }

        return $this;
    }

    /**
     * @param string|Callable $handler
     * @return $this
     */
    public function middleware($handler)
    {
        if(is_string($handler)){
            $h = explode("::", $handler);
            $handler = [(new $h[0]), $h[1]];
        }
        if(!is_callable($handler))
            throw new \InvalidArgumentException("\$handler must be a Callable");
        $this->middlewares[] = $handler;
    }

    /**
     * Устанавливает тип запроса, на который должен отвечать этот маршрут
     * @param int $type
     */
    public function setType($type = REQ_TYPE_ALL)
    {
        if($type !== REQ_TYPE_ALL || $type !== REQ_TYPE_GET || $type !== REQ_TYPE_POST)
            $type = REQ_TYPE_ALL;
        $this->requestType = $type;
    }

    /**
     * Возвращает тип запроса, на который должен отвечать маршрут
     * @return int
     */
    public function getType()
    {
        return $this->requestType;
    }

    /**
     * Тестирует путь на совпадение
     * @return bool
     */
    public function isEqualToPath($path)
    {
        //Проверяем, соответствует ли путь маршруту
        $path = Uri::preparePath($path);
        if(mb_strpos($path, $this->basePath) === 0){
            $path = Uri::preparePath(mb_substr($path, mb_strlen($this->basePath)));
            if($this->isPattern){
                if(preg_match($this->path, $path)) return true;
                return false;
            }else{
                if($this->path === $path) return true;
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * @param string $path
     * @throws \InvalidArgumentException
     * @return array
     */
    public function getPathData($path){
        $path = Uri::preparePath($path);
        if($this->isEqualToPath($path)){
            $path = Uri::preparePath(mb_substr($path, mb_strlen($this->basePath)));
            $config = Config::instance();
            $data = [
                "namespace" => $config->get("app.default_namespace"),
                "controller" => $config->get("app.default_controller"),
                "action" => $config->get("app.default_action"),
                "uriArgs" => [],
                "additionalArgs" => [],
                "handler" => $this->handler
            ];

            if($this->isPattern){
                $m = [];
                $uriArgs = [];
                preg_match($this->path, $path, $m);
                foreach($this->uriArgs as $argName => $argType){
                    if(isset($m[$argName])){
                        $val = $m[$argName];
                        switch($argType){
                            //TODO: При необходимости добавить типы данных здесь
                            case "integer":
                            case "int":
                                $val = intval($val); break;
                            case "bool":
                            case "boolean":
                                $val = ($val==="true"||$val>0); break;
                            case "float":
                            case "double":
                                $val = floatval($val); break;
                        }
                        $uriArgs[$argName] = $val;
                    }
                }
                $data['uriArgs'] = $uriArgs;
                $data['additionalArgs'] = $this->additionalArgs;
                foreach($data['additionalArgs'] as $k=>$v){
                    $m = [];
                    $data['additionalArgs'][$k] =
                    preg_replace_callback("/@\[([\w\d_]+)\]/u", function($m) use($data){
                        if(isset($data['uriArgs'][$m[1]]))
                            return $data['uriArgs'][$m[1]];
                        return "";
                    }, $v);
                }
            }else{
                $data['additionalArgs'] = $this->additionalArgs;
            }

            if(isset($data['uriArgs']['namespace']))
                $data['namespace'] = $data['uriArgs']['namespace'];
            if(isset($data['uriArgs']['controller']))
                $data['controller'] = $data['uriArgs']['controller'];
            if(isset($data['uriArgs']['action']))
                $data['action'] = $data['uriArgs']['action'];

            if(isset($data['additionalArgs']['namespace'])){
                $data['namespace'] = $data['additionalArgs']['namespace'];
                unset($data['additionalArgs']['namespace']);
            }
            if(isset($data['additionalArgs']['controller'])) {
                $data['controller'] = $data['additionalArgs']['controller'];
                unset($data['additionalArgs']['controller']);
            }
            if(isset($data['additionalArgs']['action'])){
                $data['action'] = $data['additionalArgs']['action'];
                unset($data['additionalArgs']['action']);
            }

            return $data;
        }else{
            throw new \InvalidArgumentException("Путь не совпадает с заданным маршрутом");
        }
    }

    /**
     * Возвращает все middlewares повешенные на этот маршрут
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * Хелпер, для добавления нового роута.
     * Может быть полезно при создании нескольких маршрутов
     * Обёртка для Snowy->add(...)
     * Стоит помнить, что возвращаемый объект Route - новый объект маршрута, а не тот, на котором вызван этот метод
     * @param string $path
     * @param array $additionalArgs
     * @param int $type
     * @param Callable|null $handler
     * @return Route
     */
    public function add($path, $additionalArgs = [], $type = REQ_TYPE_ALL, $handler = null){
        return Snowy::instance()->add($path, $additionalArgs, $type, $handler);
    }

}
?>