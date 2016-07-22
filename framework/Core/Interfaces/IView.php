<?php
namespace Snowy\Core\Interfaces;

interface IView{

    /**
     * Устанавливает переменные для шаблона отображения
     * @param string|array $k
     * @param string|mixed $v
     */
    public function assign($k, $v = "");

    /**
     * Производит рендеринг шаблона отображения и возвращает html-код
     * @return string
     */
    public function render();

}
?>