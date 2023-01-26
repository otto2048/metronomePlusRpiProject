<?php
    //handle sessions
    session_start();

    if (!isset($_SESSION["Uname"]))
    {
        session_destroy();
    }
?>