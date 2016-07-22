<?php
hook_add("error_404", function(\Snowy\Core\Classes\Request $req){
    echo "404 Not Found";
    echo "<br/>";
    echo "Страница по адресу " . \Snowy\Helpers\Uri::getCurrent(URI_RETURN_FULL) . " не найдена.<br/>";
});
?>