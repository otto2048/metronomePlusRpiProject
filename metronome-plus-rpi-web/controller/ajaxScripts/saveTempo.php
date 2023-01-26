<?php
    //script to add a tempo to a song
    require_once("../../model/SongModel.php");
    require_once("../Session.php");

    function addTempo()
    {
        $songModel = new SongModel();

        $result = $songModel->addTempo($_SESSION["Uname"], $_GET["title"], $_GET["value"]);

        if ($result)
        {
            echo 1;
        }
        else{
            echo 0;
        }
    }

    addTempo();

?>