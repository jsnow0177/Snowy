<?php
namespace Snowy\Core\Classes;
use Snowy\Core\Interfaces\IView;

/**
 * Class View
 * @package Snowy\Core\Classes
 */
class View implements IView{

    /**
     * @var int
     */
    protected static $nextViewId = 1;

    /**
     * @var int
     */
    protected $view_id = 0;

    /**
     * @var string
     */
    protected $view = "";

    /**
     * @var string
     */
    protected $viewFile = "";

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var array
     */
    protected $children = [];

    /**
     * @var array
     */
    protected $extends = ["", ""];

    /**
     * @var null
     */
    public static $_currentRenderView = null;

    /**
     * @param string $view Шаблон отображения
     * @return View
     */
    public static function create($view){
        $viewFile = APP_PATH . "Views" . _DS_ . $view . ".php";
        //echo $viewFile . "<br/>";
        if(file_exists($viewFile)){
            return new self($view);
        }else{
            throw new \InvalidArgumentException("Шаблон представления " . $view . " не найден");
        }
    }

    /**
     * @param $view
     */
    protected function __construct($view){
        $this->view_id = self::$nextViewId++;
        $this->view = $view;
        $this->viewFile = APP_PATH . "Views" . _DS_ . $this->view . ".php";
        $self = $this;
        hook_add("view_" . $this->view_id . "_variable_request", function($var, $default) use($self){
            if(isset($self->variables[$var])){
                echo $self->variables[$var];
            }else{
                if(is_null($default)) {
                    echo '$' . $var;
                }else{
                    echo $default;
                }
            }
        });
        filter_add("view_" . $this->view_id . "_variable_request_return", function(&$variableName, &$var) use($self){
            if(isset($self->variables[$variableName])){
                $var = $self->variables[$variableName];
            }else{
                $var = "";
            }
        });
    }

    /**
     * Устанавливает переменные для шаблона
     * @param string|array $name
     * @param string|mixed $val
     */
    public function assign($name, $val = ""){
        if(is_array($name)){
            foreach($name as $k=>$v){
                $this->variables[$k] = $v;
            }
        }else{
            $this->variables[$name] = $val;
        }
    }

    /**
     * @param View $view
     * @param string $section
     * @param bool $assignVars
     */
    public function setChild(View $view, $section, $assignVars = false){
        if(!isset($this->children[$section]))
            $this->children[$section] = [];
        if($assignVars)
            $view->assign($this->variables);
        $this->children[$section][] = $view;
    }

    /**
     * Указывает шаблон, который нужно унаследовать
     * @param string $view
     * @param string $section
     */
    protected function extnds($view, $section){
        $this->extends = [$view, $section];
    }

    /**
     * @param string $sectionName
     */
    public function section($sectionName){
        if(isset($this->children[$sectionName]) && count($this->children[$sectionName]) > 0){
            foreach($this->children[$sectionName] as $view){
                echo $view->render(true);
            }
        }
    }

    /**
     * @param bool|false $ignoreExt
     * @return string
     */
    public function render($ignoreExt = false){
        filters_apply("before_render_view", $this->view, $this);
        $prev = self::$_currentRenderView;
        self::$_currentRenderView = $this->view_id;
        ob_start();
        include($this->viewFile);
        $html = ob_get_clean();
        if(!$ignoreExt && $this->extends[0] !== "" && $this->extends[1] !== ""){
            $parentView = View::create($this->extends[0]);
            $parentView->assign($this->variables);
            $parentView->setChild($this, $this->extends[1]);
            $html = $parentView->render();
        }

        return $html;
    }

}
?>