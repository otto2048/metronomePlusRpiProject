<?php

    //action script to update a song
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/controllers/SongController.php");

    function updateSong()
    {
        $songController = new SongController();

        $songController->updateSong();
    }

    updateSong();
?>