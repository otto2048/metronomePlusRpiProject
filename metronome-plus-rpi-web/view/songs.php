<?php
    //display all the songs for this user

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
        <title>Metronome+ RPI - My Songs</title>
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
            <div class="border border-dark rounded m-auto mt-5 p-4 col-8 overflow-auto mb-5">
                <h1 class="h2">Songs:</h1>
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

            //load songs
            require_once("../model/SongModel.php");

            $songModel = new SongModel();

            $songsJSON = $songModel->getSongs($_SESSION["Uname"]);

            if ($songsJSON != null)
            {
                $songs = json_decode($songsJSON, JSON_OBJECT_AS_ARRAY);

                foreach ($songs as $song)
                {
        ?>
                    <h2 class="h3"><a href="song.php?song=<?php echo $song["SongTitle"]; ?>"><?php echo $song["SongTitle"]; ?> </a></h2>

                    <?php
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
                    ?>
                            <p>Current tempo: <?php echo $song["Tempos"][count($song["Tempos"]) - 1]["Value"] ?></p>
                    <?php
                        }
                    ?>
                    <hr>
                    
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
    </body>
</html>