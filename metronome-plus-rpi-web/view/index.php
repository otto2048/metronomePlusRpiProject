<?php
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/Session.php");

    //check user is allowed to be here
    if (!isset($_SESSION["Uname"]))
    {
        header("Location: login.php");
    }

    //check if we're loading a song
    $loading = false;
    if (isset($_GET["song"]))
    {
        $loading = true;

        //sanitize input
        require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/Validation.php");
        $validation = new Validation();

        $input = $validation->cleanInput($_GET["song"]);

        //load song details
        require_once("../model/SongModel.php");

        $songModel = new SongModel();

        $songJSON = $songModel->getSong($_SESSION["Uname"], $input);

        if ($songJSON != null)
        {
            $song = json_decode($songJSON, JSON_OBJECT_AS_ARRAY);
        }
        else
        {
            $loading = false;
        }
    }

    require_once("orderTempos.php");

?>

<!doctype html>

<html lang="en">
    <head>
        <title>
            <?php
                if (!$loading)
                {
                    echo "Metronome+ RPI - Freeplay";
                }
                else
                {
                    echo "Metronome+ RPI - ".$song["SongTitle"];
                }
            ?>
        </title>
        <?php include "head.php"; ?>
    </head>
    <body>
        <?php 
            function getHeader()
            {
                $selected = "index.php";
                include "navigation.php";
            }

            getHeader();

         ?>

        <div class="container">
            <div class="mt-5 border border-dark p-3 d-flex justify-content-center rounded mb-3">
                <p id="bpm-value" class=" m-0 h1">
                    <?php
                        if ($loading && isset($song["Tempos"]))
                        {
                            //sort tempos by creation date
                            usort($song["Tempos"], "orderTempos");
                            
                            echo $song["Tempos"][0]["Value"];
                        }
                        else
                        {
                            echo 80;
                        }
                    ?>
                </p>
            </div>
            
            <div class="d-flex justify-content-center">
                <button class="btn btn-dark ps-3 pe-3 ms-1 me-1" id="down-btn" disabled aria-disabled="true"><b>-</b></button>
                <button class="btn btn-dark ps-3 pe-3 ms-1 me-1" id="down-10-btn" disabled aria-disabled="true"><b>-10</b></button>
                <button class="btn btn-danger ps-3 pe-3 ms-1 me-1" id="play-btn" disabled  aria-disabled="true"><span class="mdi mdi-play" id="play-pause-btn"></span></button>
                <button class="btn btn-dark ps-3 pe-3 ms-1 me-1" id="up-10-btn" disabled aria-disabled="true"><b>+10</b></button>
                <button class="btn btn-dark ps-3 pe-3 ms-1 me-1" id="up-btn" disabled aria-disabled="true"><b>+</b></button>
            </div>

            <?php
                if (!$loading)
                {
            ?>

                    <div class="row d-flex align-items-center pt-2">
                        <div class="col-2">
                            <button class="btn btn-dark ps-3 pe-3 ms-1 me-1" type="button" id="load-btn">Load</button>
                        </div>
                        <div class="col-8">
                            <p class="text-center m-0"><b>Freeplay mode</b></p>
                        </div>
                        <div class="col-2">
                            <a class="btn btn-dark ps-3 pe-3 ms-1 me-1 float-end" href="createSong.php?currentTempo=80" role="button" id="save-btn">Save</a>
                        </div>
                    </div>

                    <p class="text-center mt-3">Metronome is in freeplay mode, load a song to set a saved tempo</p>

            <?php
                }
                else
                {
            ?>
                    <div class="border border-dark rounded mt-3 mb-3">
                        <p class="h4 m-0 pt-3 text-center"><b><a href="song.php?song=<?php echo $song["SongTitle"]; ?>"><?php echo $song["SongTitle"]; ?></a> | Target tempo: <?php echo $song["Target"]; ?></b></p>
                        <span class="d-none"  id="song-title"><?php echo $song["SongTitle"]; ?></span>
                        <p class="text-center m-0 pb-3">Artist: <?php echo $song["Artist"]; ?> </p>
                    </div>
                        <?php
                            //check if tempos exist
                            if ($song["Tempos"])
                            {
                                //check if tempos is empty
                                if (count($song["Tempos"]) > 0)
                                {

                        ?>
                    
                    <div class="overflow-auto" style="max-height: 200px">

                        <ul class="text-center mt-3 list-unstyled list-group" id="tempos-list">
                            <li class="list-group-item border-dark h4 pt-3 pb-3 m-0 sticky-top"><b>Tempos:</b></li>
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

                    <button class="btn btn-dark ps-3 mt-3 pe-3 ms-1 me-1 float-end" id="save-tempo-btn">Save tempo</button>

                    <?php
                            }
                        }
                    ?>

            <?php
                }
            ?>
        </div>

        <!-- load song modal -->
        <div class="modal fade" id="load-song-modal" tabindex="-1" aria-labelledby="load-song-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h5 modal-title">Select a song</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-dark btn" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- load metronome connection modal -->
        <div class="modal fade" id="load-metronome-modal" tabindex="-1" aria-labelledby="load-metronome-modal" aria-hidden="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h5 modal-title">Preparing metronome...</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-body">
                        <div class="d-flex align-items-center" id="connecting">
                            <strong>Connecting to metronome....</strong>
                            <div class="spinner-border ms-auto" role="status" aria-hidden="true" id="spinner"></div>
                        </div>
                        <p>Status: <span id="metronome-load-status">Loading</span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-dark btn" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="module" src="js/client.js"></script>
    </body>
</html>