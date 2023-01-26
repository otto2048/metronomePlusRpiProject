<!-- The navigation for all the web pages -->
<!doctype html>

<html lang="en">
    <head></head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <div class="d-flex">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <a class="navbar-brand" href="index.php">Metronome+ RPI</a>
                </div>
                <!--        determining which <li> should have the selected ID-->
                <div name="selectedLink" <?php
                    if (isset($selected))
                    {
                        echo "class=".$selected;
                    }
                ?>></div>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <?php 
                            if (isset($_SESSION["Uname"]))
                            {
                                
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Metronome</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="songs.php">My Songs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>

                        <?php
                            }
                            else
                            {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>

                        <?php
                            }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
        <script src="js/navigation.js"></script>
    </body>
</html>