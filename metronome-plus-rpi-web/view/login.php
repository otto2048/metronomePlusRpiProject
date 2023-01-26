<!-- login to metronome -->

<?php

    //handle starting session
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/Session.php");

    //check if user is already logged in
    if (isset($_SESSION['Uname']))
    {
        header("Location: index.php");
    }
?>

<!doctype html>

<html lang="en">
    <head>
        <title>Metronome+ RPI - Login</title>
        <?php include "head.php"; ?>
    </head>
    <body>
        <?php 
            function getHeader()
            {
                $selected = "login.php";
                include "navigation.php";
            }

            getHeader();
        ?>

        <div class="container">
            <div class="border border-dark rounded m-auto mt-5 p-4 col-8 overflow-auto">
                <h1 class="h2">Login:</h1>
                <form id="form" name="form" method="post" action="../controller/actionScripts/login.php">
                    <div class="form-group"> 
                        <label for="uname">Enter Username:</label>
                        <input type="text" class="form-control" name="uname" id="uname" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Enter Password:</label>
                        <input type="password" class="form-control" name="password" id="password" required/>
                    </div>
                    <input type="submit" class="btn btn-dark mt-2 float-end" name="button" value="Login"/>
                </form>
            </div>
        </div>
    </body>
</html>