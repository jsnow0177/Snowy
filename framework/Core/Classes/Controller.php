<?php
namespace Snowy\Core\Classes;
use Snowy\Core\Snowy;

/**
 * Class Controller
 * @package Snowy\Core\Classes
 */
class Controller{

    /**
     * @var string
     */
    protected $controllerClass;

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Snowy
     */
    protected $app;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response){
        $this->controllerClass = get_called_class();
        $this->controllerName = explode("\\", $this->controllerClass);
        $this->controllerName = $this->controllerName[count($this->controllerName)-1];
        $this->request = $request;
        $this->response = $response;
        $this->app = Snowy::instance();
    }

    /**
     * Выполняет обработку запроса
     * @throws \BadMethodCallException
     * @return Response
     */
    public final function handleRequest(){
        $requestedAction = $this->request->action;
        $realActionName = "action" . $requestedAction;
        hooks_apply("controller_handleRequest", $this->controllerClass, $this->controllerName, $requestedAction);
        if(method_exists($this, $realActionName)){
            $result = true;
            try {
                $result = $this->beforeRun();
                if (!is_bool($result)) $result = true;
            }catch(\Exception $ex){
                $result = false;
                $this->handleRunException($ex);
            }

            if($result){
                ob_start();
                $execResult = $this->$realActionName();
                ob_end_clean();
                $this->onResponse($execResult);
                if($execResult !== null && $execResult !== "")
                    $this->response->content($execResult);
            }

            return $this->response;
        }else{
            hooks_apply("controller_handleRequest_404", $this->controllerClass, $this->controllerName, $requestedAction);
            throw new \BadMethodCallException();
        }
    }

    /**
     * @return bool
     */
    protected function beforeRun(){
        return true;
    }

    /**
     * @param \Exception $ex
     */
    protected function handleRunException(\Exception $ex){

    }

    /**
     * @param mixed $result
     */
    protected function onResponse(&$result){

    }

}
?>