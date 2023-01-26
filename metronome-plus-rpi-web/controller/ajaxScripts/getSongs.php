<?php
    //script to load songs for a user
    require_once("../../model/SongModel.php");
    require_once("../Session.php");

    function getSongs()
    {
        $songModel = new SongModel();

        $songsJSON = $songModel->getSongs($_SESSION["Uname"]);

        if ($songsJSON == null)
        {
            echo 0;
        }
        else{
            echo $songsJSON;
        }
    }

    getSongs();

?>