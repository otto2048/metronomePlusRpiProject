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
        <title>Metronome+ RPI - Save Song</title>
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

            require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/Validation.php");
            $validation = new Validation();

            //sanitize input
            $input = $validation->cleanInput($_GET["currentTempo"]);

            
        ?>
            <div class="container">
                <div class="border border-dark rounded m-auto mt-5 p-4 col-8 overflow-auto">

                    <h1 class="h3">Save Song</h1>

                    <hr>

                    <?php
                        //check for errors on this page
                        if (isset($_GET["message"]))
                        {
                            $message = $_GET["message"];
                        
                    ?>
                        <!-- output errors -->
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

                            <?php
                                $messageobj = json_decode($message, JSON_OBJECT_AS_ARRAY);

                                foreach ($messageobj as $item => $value)
                                {
                                    echo $validation->cleanInput($value)."<br>";
                                }
                            ?>

                        </div>
                    <?php
                        }
                    ?>

                    <form role="form" method="POST" action="../controller/actionScripts/createSong.php">
                        <div class="form-group">
                            <label for="SongTitle">Title:</label>
                            <input type="text" class="form-control" name="SongTitle" required id="SongTitle">
                        </div>
                        <div class="form-group">
                            <label for="Artist">Artist:</label>
                            <input type="text" class="form-control" name="Artist" id="Artist">
                        </div>
                        <div class="form-group">
                            <label for="Target">Target Tempo:</label>
                            <input type="text" class="form-control" name="Target" required id="Target">
                        </div>
                        <div class="form-group">
                            <label for="Current">Current Tempo:</label>
                            <input type="text" class="form-control" name="Current" readonly required value="<?php echo $input ?>" id="Current">
                        </div>
                        <button class="btn btn-dark float-end mt-2" type="submit">Submit</button>
                    </form>
         
                </div>
            </div>
    </body>
</html>