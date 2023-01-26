<?php
    //script to get session data
    require_once("../Session.php");

    function getSessionData()
    {
        $sessionVariables = new \stdClass();
        $sessionVariables -> Uname = $_SESSION["Uname"];
        $sessionVariables -> PiClientId = $_SESSION["PiClientId"];

        //echo json string of session data variables
        echo json_encode($sessionVariables);
    }

    getSessionData();

?>