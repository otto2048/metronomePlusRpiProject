<?php
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/Session.php");

    //check user is allowed to be here
    if (!isset($_SESSION["Uname"]))
    {
        header("Location: login.php");
    }
?>

<!doctype html>

<html lang="en">
    <head>
        <title>Metronome+ RPI - View Song</title>
        <?php include "head.php"; ?>
    </head>
    <body>
        <?php 
            function getHeader()
            {
                $selected = "songs.php";
                include "navigation.php";
            }

            getHeader();
        ?>
            <div class="container">
            <div class="border border-dark rounded m-auto mt-5 p-4 col-8 overflow-auto">
        <?php

            //load songs
            require_once("../model/SongModel.php");

            require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/Validation.php");
            $validation = new Validation();

            //sanitize input
            $input = $validation->cleanInput($_GET["song"]);

            $songModel = new SongModel();

            $songJSON = $songModel->getSong($_SESSION["Uname"], $input);

            if ($songJSON != null)
            {
                $song = json_decode($songJSON, JSON_OBJECT_AS_ARRAY);
         ?>

                <div class="row">
                    <div class="col">
                        <h1 class="h3"><?php echo $song["SongTitle"]; ?></h1>
                    </div>
                    <div class="col">

                        <button class="btn btn-danger ps-3 pe-3 ms-1 me-1 float-end mb-1" id="delete-btn">Delete <span class="mdi mdi-trash-can"></span></button>

                        <a href="updateSong.php?song=<?php echo $input ?>" class="btn btn-dark ps-3 pe-3 ms-1 me-1 float-end mb-1" role="button" id="edit-btn">Edit <span class="mdi mdi-lead-pencil"></span></a>
                        <a href="index.php?song=<?php echo $input ?>" class="btn btn-dark ps-3 pe-3 ms-1 me-1 float-end mb-1" role="button" id="load-btn">Load <span class="mdi mdi-disc-player"></span></a>
                    </div>
                </div>

                <hr>

                <?php
                    //check for errors on this page
                    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/Validation.php");
                    $validation = new Validation();

                    if (isset($_GET["message"]))
                    {
                        $message = $_GET["message"];
                        $messageobj = json_decode($message, JSON_OBJECT_AS_ARRAY);
                    
                ?>

                <!-- output messages -->
                <div class="alert <?php if ($messageobj["success"] == "true") { echo "alert-success";} else {echo "alert-danger";} ?> alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

                    <?php
                        echo $validation->cleanInput($messageobj["content"])."<br>";
                    ?>

                </div>

                <?php
                    }

                    if (isset($song["Artist"]))
                    {
                ?>
                        <p>Artist: <?php echo $song["Artist"]; ?> </p>
                <?php
                    }
                ?>

                <p>Target tempo: <?php echo $song["Target"]; ?> </p>

                <?php
                    if (isset($song["Tempos"]))
                    {
                        require_once("orderTempos.php");

                        if (count($song["Tempos"]) > 1)
                        {
                            //sort tempos by date
                            usort($song["Tempos"], "orderTempos");
                        }

                ?>
                        <p>Current tempo: <?php echo $song["Tempos"][0]["Value"] ?></p>
                        
                        <div class="overflow-auto" style="max-height: 200px">

                            <ul class="text-center mt-3 list-unstyled list-group" id="tempos-list">
                                <li class="list-group-item border-dark h4 pt-3 pb-3 m-0 sticky-top">Tempos:</li>
                                <?php
                                    foreach ($song["Tempos"] as $tempo)
                                    {
                                        ?>
                                            <li class="list-group-item border-dark"> <?php echo $tempo["Value"]; ?> </li>
                                        <?php
                                    }
                                ?>
                            </ul>


                        </div>
         <?php
                    }
            }
            else
            {
                echo "Failed to load songs";
            }
         ?>
         
         </div>
        </div>

        <!-- delete song modal -->
        <div class="modal fade" id="delete-song-modal" tabindex="-1" aria-labelledby="load-song-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h5 modal-title">Are you sure you want to delete this song?</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-body">
                        <p>Confirm deletion</p>
                        <p>All song and tempo data will be lost!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-dark btn" data-bs-dismiss="modal">Close</button>
                        <a href="../controller/actionScripts/deleteSong.php?SongTitle=<?php echo $song["SongTitle"]; ?>" class="btn btn-danger ps-3 pe-3 ms-1 me-1 float-end mb-1" role="button" id="delete-btn">Delete <span class="mdi mdi-trash-can"></span></a>
                    </div>
                </div>
            </div>
        </div>

        <script src="js/song.js"></script>
    </body>
</html>