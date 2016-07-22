<?php
namespace App\Controllers;
use Snowy\Core\Classes\Controller;

class Main extends Controller{

    protected function actionIndex(){
        $this->response->manageContentType = true;
        $this->response->contentType("text/html");
        return "Hello World!";
    }

    protected function actionJsonExample(){
        return [
            "a" => 1,
            "b" => 2,
            "c" => "abc",
            "d" => [
                "a", "b", "c",
                "i" => [
                    "a" => true,
                    "b" => 0.515,
                    "c" => 98,
                    "d" => new \stdClass()
                ]
            ]
        ];
    }

}
?>