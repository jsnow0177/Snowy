<?php
namespace Snowy\Core\Interfaces;

interface IRoute{

    /**
     * @param string $basePath Базовый маршрут (маршрут, на котором "висит" приложение)
     * @param string $path
     * @param array $additionalArgs
     * @param Callable|null $handler
     */
    function __construct($basePath, $path, $additionalArgs = [], $handler = null);

    /**
     * Устанавливает типы для URI-аргументов
     * @param array $types
     */
    function setArgsTypes($types = []);

    /**
     * @param string|Callable $handler
     * @return $this
     */
    function middleware($handler);

    /**
     * Устанавливает тип запроса, на который должен отвечать этот маршрут
     * @param int $type
     */
    function setType($type = REQ_TYPE_ALL);

    /**
     * Возвращает тип запроса, на который должен отвечать маршрут
     * @return int
     */
    function getType();

    /**
     * Тестирует путь на совпадение
     * @return bool
     */
    function isEqualToPath($path);

    /**
     * Возвращает все middlewares повешенные на этот маршрут
     * @return array
     */
    function getMiddlewares();

    /**
     * @param string $path
     * @return array
     */
    function getPathData($path);

}
?>