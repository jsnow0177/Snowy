<?php
#region Функции быстрого доступа к объектам
/**
 * @return \Snowy\Core\Snowy
 */
function app(){
    return \Snowy\Core\Snowy::instance();
}

/**
 * @return \Snowy\Core\Config
 */
function config(){
    return \Snowy\Core\Config::instance();
}

/**
 * @param string $view
 * @throws InvalidArgumentException
 * @return \Snowy\Core\Classes\View
 */
function view_create($view){
    return \Snowy\Core\Classes\View::create($view);
}

/**
 * Осуществляет быстрый доступ к переменной текущего представления и выводит её
 * @param string $variableName
 * @param string|null $default
 */
function v($variableName, $default = null){
    hooks_apply("view_" . \Snowy\Core\Classes\View::$_currentRenderView . "_variable_request", $variableName, $default);
}

/**
 * Осуществляет быстрый доступ к переменной текущего представления и возвращает её
 * @param string $variableName
 * @return mixed
 */
function ev($variableName){
    $var = null;
    filters_apply("view_" . \Snowy\Core\Classes\View::$_currentRenderView . "_variable_request_return", $variableName, $var);
    return $var;
}
#endregion

#region Функции хуков
/**
 * Регистрирует зацепку для события
 * @param string $action Событие
 * @param Callable $handler Обработчик события
 * @return bool|\Snowy\Core\Interfaces\IHook
 */
function hook_add($action, Callable $handler){
    $hook = new \Snowy\Core\Classes\Hook($action, $handler);
    $res = \Snowy\Core\HooksManager::registerHook(new \Snowy\Core\Classes\Hook($action, $handler));
    if($res)
        return $hook;
    return false;
}

/**
 * Удаляет зацепку
 * @param \Snowy\Core\Interfaces\IHook $hook Объект зацепки
 * @return bool
 */
function hook_remove(\Snowy\Core\Interfaces\IHook $hook){
    return \Snowy\Core\HooksManager::unregisterHook($hook);
}

/**
 * Возвращает количество зарегистрированных зацепок
 * @return int
 */
function hooks_count(){
    return \Snowy\Core\HooksManager::getCount();
}

/**
 * Запускает событие
 * @param string $action
 * @param ...$args
 */
function hooks_apply($action, ...$args){
    array_unshift($args, $action);
    call_user_func_array(array("Snowy\\Core\\HooksManager", "runAction"), $args);
}
#endregion

#region Функции фильтров
/**
 * Регистрирует фильтр для события
 * @param string $action Событие
 * @param callable $handler Обработчик события
 * @param int $priority Приоритет
 * @return bool|\Snowy\Core\Interfaces\IFilter
 */
function filter_add($action, Callable $handler, $priority = FILTER_PRIORITY_LOW){
    $filter = new \Snowy\Core\Classes\Filter($action, $handler, $priority);
    $res = \Snowy\Core\FiltersManager::registerFilter($filter);
    if($res)
        return $filter;
    return false;
}

/**
 * Удаляет фильтр
 * @param \Snowy\Core\Interfaces\IFilter $filter
 * @return bool
 */
function filter_remove(\Snowy\Core\Interfaces\IFilter $filter){
    return \Snowy\Core\FiltersManager::unregisterFilter($filter);
}

/**
 * Возвращает количество зарегистрированных фильтров
 * @return int
 */
function filters_count(){
    return \Snowy\Core\FiltersManager::getCount();
}

/**
 * Запускает событие для фильтров
 * @param string $action
 * @param ...$args
 */
function filters_apply($action, &...$args){
    array_unshift($args, $action);
    call_user_func_array(array("Snowy\\Core\\FiltersManager", "runAction"), $args);
}
#endregion

#region Хелперы для работы с URI
/**
 * @param int $flag
 * @return string
 */
function uri_getCurrent($flag = URI_RETURN_PATH){
    return \Snowy\Helpers\Uri::getCurrent($flag);
}

/**
 * @return string
 */
function uri_assetsLink(){
    return \Snowy\Helpers\Uri::assetsLink();
}

/**
 * @param string $asset
 * @return string
 */
function assetLink($asset){
    $assets_link = uri_assetsLink();
    if(mb_strrpos($assets_link, "/") === mb_strlen($assets_link)-1)
        $assets_link = mb_substr($assets_link, 0, mb_strlen($assets_link)-2);
    if(mb_strpos($asset, "/") === 0)
        $asset = mb_substr($asset, 1);
    return $assets_link . "/" . $asset;
}
#endregion
?>