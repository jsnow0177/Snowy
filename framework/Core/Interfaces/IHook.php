<?php
namespace Snowy\Core\Interfaces;

/**
 * Interface IHook
 * @package Snowy\Core\Interfaces
 */
interface IHook{

    /**
     * @param string $hookAction
     * @param Callable $hookHandler
     */
    function __construct($hookAction, Callable $hookHandler);

    /**
     * @return string
     */
    function getAction();

    /**
     * @return int
     */
    function getId();

    /**
     * @param ... $arguments
     * @return mixed
     */
    function runHandler(...$args);

}
?>