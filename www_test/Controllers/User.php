<?php
namespace App\Controllers;
use Snowy\Core\Classes\Controller;
use Snowy\Core\Classes\View;

class User extends Controller{

    protected function actionShow(){
        $uid = $this->request->args->get("user_id");
        $v = View::create("user");
        $v->assign([
            "title" => "User ID is " . $uid,
            "user_id" => $uid
        ]);

        return $v;
    }

}
?>