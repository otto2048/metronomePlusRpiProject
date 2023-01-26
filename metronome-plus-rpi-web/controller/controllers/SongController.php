<?php
    //include validation
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/Validation.php");

    //include model classes
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/model/SongModel.php");

    Class SongController
    {
        //validation object
        private $validationObj;

        //song model object
        private $songModelObj;

        public function __construct()
        {
            //init validation object
            $this->validationObj = new Validation();

            //init song model object
            $this->songModelObj = new SongModel();

            //begin the session
            session_start();
        }

        public function createSong()
        {
            //user input
            $songTitleInput = $_POST["SongTitle"];
            $artistInput = $_POST["Artist"];
            $targetTempoInput = $_POST["Target"];
            $currentTempoInput = $_POST["Current"];

            //sanitize and validate input

            $songTitle = $this->validationObj->cleanInput($songTitleInput);
            $artist = $this->validationObj->cleanInput($artistInput);
            $targetTempo = $this->validationObj->cleanInput($targetTempoInput);
            $currentTempo = $this->validationObj->cleanInput($currentTempoInput);

            //object to validate with/send data to the model
            $data = new \stdClass();
            $data -> Uname = $_SESSION["Uname"];
            $data -> SongTitle = $songTitle;
            $data -> Artist = $artist;
            $data -> Target = $targetTempo;
            $data -> Current = $currentTempo;
            $data -> Tempos = array(array("Value" => $currentTempo, "Created" => time()));

            $jsonData = json_encode($data, JSON_OBJECT_AS_ARRAY);

            //validate song
            $validated = $this->validationObj->validateSong($jsonData);

            if ($validated)
            {
                //return user to create song page
                echo '<script type="text/javascript">window.open("/cmp408/metronome-plus-rpi-web/view/createSong.php?currentTempo='.$currentTempo.'&message='.urlencode($validated).'", name="_self")</script>';
            }
            else
            {
                //call createSong in model
                $result = $this->songModelObj->putSong($jsonData);

                if ($result)
                {
                   echo '<script type="text/javascript">window.open("/cmp408/metronome-plus-rpi-web/view/song.php?song='.$songTitle.'", name="_self")</script>';
                }
                else
                {
                    echo '<script type="text/javascript">window.open("/cmp408/metronome-plus-rpi-web/view/createSong.php?currentTempo='.$currentTempo.'&message=Failed to save song", name="_self")</script>';
                }
            }
        }

        public function updateSong()
        {
            //user input
            $songTitleInput = $_POST["SongTitle"];
            $artistInput = $_POST["Artist"];
            $targetTempoInput = $_POST["Target"];

            //  sanitize and validate input

            $songTitle = $this->validationObj->cleanInput($songTitleInput);
            $artist = $this->validationObj->cleanInput($artistInput);
            $targetTempo = $this->validationObj->cleanInput($targetTempoInput);

            //object to validate with/send data to the model
            $data = new \stdClass();
            $data -> Artist = $artist;
            $data -> Target = $targetTempo;

            $jsonData = json_encode($data, JSON_OBJECT_AS_ARRAY);

            //validate song
            $validated = $this->validationObj->validateSong($jsonData);

            if ($validated)
            {
                //return user to update song page
                echo '<script type="text/javascript">window.open("/cmp408/metronome-plus-rpi-web/view/updateSong.php?message='.urlencode($validated).'&song='.$songTitle.'", name="_self")</script>';
            }
            else
            {
                //call updateSong in model
                $result = $this->songModelObj->updateSong($_SESSION["Uname"], $songTitle, $jsonData);

                if ($result)
                {
                   echo '<script type="text/javascript">window.open("/cmp408/metronome-plus-rpi-web/view/song.php?song='.$songTitle.'", name="_self")</script>';
                }
                else
                {
                    echo '<script type="text/javascript">window.open("/cmp408/metronome-plus-rpi-web/view/updateSong.php?song='.$songTitle.'&currentTempo='.$currentTempo.'&message=Failed to save song", name="_self")</script>';
                }
            }
        }

        public function deleteSong()
        {
            //user input
            $songTitleInput = $_GET["SongTitle"];

            $songTitle = $this->validationObj->cleanInput($songTitleInput);

            $result = $this->songModelObj->deleteSong($_SESSION["Uname"], $songTitle);

            $message = array();

            if ($result)
            {
                //success, send to songs page
                $message["success"] = "true";
                $message["content"] = "Successfully deleted song!";

                $messageJSON = json_encode($message);

                echo '<script type="text/javascript">window.open("/cmp408/metronome-plus-rpi-web/view/songs.php?message='.urlencode($messageJSON).'", name="_self")</script>';
            }
            else
            {
                //failure, send to song page
                $message["success"] = "false";
                $message["content"] = "Failed to delete song";

                $messageJSON = json_encode($message);

                echo '<script type="text/javascript">window.open("/cmp408/metronome-plus-rpi-web/view/song.php?song='.$songTitle.'&message='.urlencode($messageJSON).'", name="_self")</script>';
            }
        }

    }

?>