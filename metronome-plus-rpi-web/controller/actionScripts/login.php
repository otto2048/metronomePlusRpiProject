<?php
    //action script to login a user
    
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/controllers/LoginController.php");

    function actionLoginUser()
    {
        $loginController = new LoginController();

        $loginController -> loginUser();
    }

    actionLoginUser();
?>