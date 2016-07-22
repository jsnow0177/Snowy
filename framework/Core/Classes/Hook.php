<?php
namespace Snowy\Core\Classes;
use Snowy\Core\HooksManager;
use Snowy\Core\Interfaces\IHook;

/**
 * Class Hook
 * @package Snowy\Core\Classes
 */
class Hook implements IHook{

    /**
     * @var int
     */
    private static $nextId = 1;

    /**
     * @var string
     */
    protected $hookAction;

    /**
     * @var null|Callable
     */
    protected $hookHandler;

    /**
     * @var int ID зацепки
     */
    private $hookId;

    /**
     * @return int
     */
    protected static function getNextId(){
        return self::$nextId++;
    }

    /**
     * @param string $hookAction
     * @param Callable $hookHandler
     */
    public function __construct($hookAction, Callable $hookHandler)
    {
        $this->hookHandler = $hookHandler;
        $this->hookAction = mb_strtolower($hookAction);
        $this->hookId = self::getNextId();
    }

    /**
     * @return int
     */
    public final function getId()
    {
        return $this->hookId;
    }

    /**
     * @return string
     */
    public final function getAction()
    {
        return $this->hookAction;
    }

    /**
     * @param ... $args
     * @return mixed
     */
    public final function runHandler(...$args)
    {
        $executionResult = call_user_func_array($this->hookHandler, $args);
        if(!is_bool($executionResult))
            $executionResult = true;
        return $executionResult;
    }

}
?>