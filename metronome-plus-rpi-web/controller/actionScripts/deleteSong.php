<?php

    //action script to delete a song
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/controllers/SongController.php");

    function deleteSong()
    {
        $songController = new SongController();

        $songController->deleteSong();
    }

    deleteSong();
?>