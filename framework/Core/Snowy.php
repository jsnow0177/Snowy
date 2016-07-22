<?php
namespace Snowy\Core;
use Snowy\Core\Classes\CapturedEvent;
use Snowy\Core\Classes\Request;
use Snowy\Core\Classes\Response;
use Snowy\Core\Classes\Route;
use Snowy\Core\Classes\Singleton;
use Snowy\Core\Interfaces\IRoute;
use Snowy\Helpers\Str;
use Snowy\Helpers\Uri;

/**
 * Class Snowy
 * @package Snowy\Core
 */
class Snowy extends Singleton{

    /**
     * @var bool
     */
    private static $captureEvents = false;

    /**
     * @var array Записанные события
     */
    private static $capturedEvents = [];

    /**
     * @var bool
     */
    private $basicalRequestAlreadyHandled = false;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $base_path;

    /**
     * @var array Маршруты
     */
    private $routes = [];

    /**
     * @var null|Request
     */
    private static $currentRequest = null;

    /**
     * @var null|Response
     */
    private static $currentResponse = null;

    /**
     * Определяет, нужно ли записывать события сгенерированные HooksManager и FiltersManager
     * Полезно, если нужно узнать в какой последовательности выполняются события
     * @param bool $flag
     */
    public static function captureEvents($flag){
        if(!is_bool($flag))
            $flag = true;
        if($flag === false)
            self::$capturedEvents = [];
        self::$captureEvents = $flag;
    }

    /**
     * Записывает событие
     * @param string $eventName
     * @param string $type "filter"||"hook"
     * @param array $types Типы передаваемых аргументов
     */
    public static function captureEvent($eventName, $type, $types = []){
        if(self::$captureEvents){
            self::$capturedEvents[] = new CapturedEvent($eventName, $type, $types);
        }
    }

    /**
     * Возвращает массив захваченных событийы
     * @return array
     */
    public static function getCapturedEvents(){
        return self::$capturedEvents;
    }

    /**
     * Конструктор
     */
    protected function __construct(){
        $this->config = Config::instance();
        $this->base_path = Uri::preparePath($this->config->get("app.uri"));
    }

    /**
     * @param string $path
     * @param array $additionalArgs
     * @param int $type
     * @param Callable|null $handler
     * @return Route
     */
    public function add($path, $additionalArgs = [], $type = REQ_TYPE_ALL, $handler = null){
        $pathLength = Uri::getLength($path);
        $route = new Route($this->base_path, $path, $additionalArgs, $handler);
        $route->setType($type);
        $this->routes[] = [$path, $route, $pathLength];

        //Сортируем массив маршрутов по длине маршрута
        //Короткие - в конце массива
        usort($this->routes, function($a, $b){
            if($a[2] > $b[2]) return -1;
            if($b[2] < $a[2]) return 1;
            return 0;
        });

        return $route;
    }

    /**
     * Устанавливает middleware на любой маршрут.
     * Если нужно установить middleware на все маршруты достаточно присвоить $path значение "" или "*"
     * @param string $path
     * @param Callable|string $handler
     * @return $this
     */
    public function middleware($path, $handler){
        $path = Uri::preparePath($path);
        if(is_string($handler)) {
            $h = explode("::", $handler);
            $handler = [(new $h[0]), $h[1]];
        }
        if(!is_callable($handler))
            throw new \InvalidArgumentException("\$handler must be a string or a Callable");

        if($path === "" || $path === "*"){
            //Middleware нужно присоединить ко всем маршрутам
            foreach($this->routes as $k=>&$v){
                $v[1]->middleware($handler);
            }
        }else{
            foreach($this->routes as $k=>&$v){
                if($v[0] === $path){
                    $v[1]->middleware($handler);
                }
            }
        }

        return $this;
    }

    public function captureRequest(Request $request = null){
        $isInternal = true;
        if(is_null($request) && !$this->basicalRequestAlreadyHandled){
            //
            $request = new Request();
            $request->path = Uri::getCurrent();
            $request->get = $_GET;
            $request->post = $_POST;
            $request->files = $_FILES;
            $request->requestType = (count($_POST)>0)?REQ_TYPE_POST:REQ_TYPE_GET;
            $request->ip = $_SERVER['REMOTE_ADDR'];
            $isInternal = false;
            $this->basicalRequestAlreadyHandled = true;
        }

        if(is_null($request)){
            throw new \Exception("Базовый запрос уже обработан!");
        }

        filters_apply("captureRequest", $request, $isInternal);

        $response = new Response();

        //Выполняем поиск соответствующего маршрута
        $routeFounded = false;
        foreach($this->routes as $routeInfo){
            $route = $routeInfo[1];
            if($route->isEqualToPath($request->path)){
                //Мы нашли нужный нам маршрут
                $routeFounded = true;
                $pathData = $route->getPathData($request->path);
                $controller_namespace = $pathData['namespace'];
                $controller = $pathData['controller'];
                $action = $pathData['action'];
                $uriArgs = $pathData['uriArgs'];
                $additionalData = $pathData['additionalArgs'];

                $args = array_replace_recursive($additionalData, $uriArgs);
                $request->args = $args;
                $request->namespace = Str::ucAfter(Str::toCamelCase($controller_namespace));
                $request->controller = Str::ucAfter(Str::toCamelCase($controller));
                $request->action = Str::toCamelCase($action);

                filters_apply("captureRequest_after", $request, $response);

                //Выполняем обработку
                $controllerClass = $this->config->get("app.namespace_prefix")
                    . "\\" . (($request->namespace==="")?"":$request->namespace . "\\")
                    . "Controllers\\" . $request->controller;
                $controllerObject = null;

                //echo $controllerClass . "<br/>";

                if(class_exists($controllerClass)){
                    $controllerObject = new $controllerClass($request, $response);
                    $controllerObject->handleRequest();
                    if($isInternal)
                        return $response;
                    hooks_apply("shutdown", $this);
                    $response->end();
                    exit;
                }elseif(!$isInternal){
                    filters_apply("error_404", $request, $response);
                }

                break;
            }
        }

        if(!$routeFounded && !$isInternal){
            filters_apply("error_404", $request, $response);
        }

        if(!$isInternal){
            hooks_apply("shutdown", $this);
            $response->end();
            exit;
        }

        return $response;
    }

}
?>