<?php

    //action script to create a song
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/controllers/SongController.php");

    function createSong()
    {
        $songController = new SongController();

        $songController->createSong();
    }

    createSong();
?>