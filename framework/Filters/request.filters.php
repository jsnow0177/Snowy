<?php
filter_add("captureRequest", function(\Snowy\Core\Classes\Request &$req, $isInternal){
    //Выполняет очистку данных
    if(!$isInternal){
        $props = ["args", "get", "post"];
        foreach($props as $v){
            $req->$v = \Snowy\Helpers\Arr::sanitize($req->$v);
        }
    }
}, FILTER_PRIORITY_SYSTEM);

filter_add("error_404", function(\Snowy\Core\Classes\Request &$req, \Snowy\Core\Classes\Response &$res){
    $res->setStatus(404)
        ->content("Страница по адресу " . \Snowy\Helpers\Uri::getCurrent(URI_RETURN_FULL) . " не найдена")
        ->contentType("text/html");
}, FILTER_PRIORITY_LOW);
?>