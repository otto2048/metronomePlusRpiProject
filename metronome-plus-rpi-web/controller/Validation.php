<?php

    Class Validation
    {
        //database data constraints
        const USERNAME_LENGTH = 30;

        const SONG_TITLE_LENGTH = 30;
        const ARTIST_LENGTH = 20;

        const MAX_BPM = 250;
        const MIN_BPM = 31;
        
        //sanitizing input
        public function cleanInput($input)
        {
            $retValue = trim($input);
            $retValue = stripslashes($retValue);
            $retValue = htmlspecialchars($retValue);

            return $retValue;
        }

        //validating a string in terms of a length
        public function validateString($input, $length)
        {
            if (strlen($input) <= $length)
            {
                return true;
            }

            return false;
        }

        //validating an integer
        private function validateInt($input)
        {
            return ctype_digit($input);
        }

        //validate tempo
        private function validateTempo($input)
        {
            //check tempo is in range
            if (intval($input) > Validation::MAX_BPM || intval($input) <= Validation::MIN_BPM)
            {
                return false;
            }

            return true;
        }

        //validating a song object
        //on success, return null
        //on failure, return JSON string with error messages
        public function validateSong($jsonData)
        {
            $returnMessage = array();

            $song = json_decode($jsonData, JSON_OBJECT_AS_ARRAY);

            //if exists, check song name
            if (isset($song["SongTitle"]))
            {
                if (!$this->validateString(($song["SongTitle"]), Validation::SONG_TITLE_LENGTH))
                {
                    $returnMessage["songTitle"] = "Song title must be ".Validation::SONG_TITLE_LENGTH." characters or under!";
                }
            }

            //check artist
            if (!$this->validateString(($song["Artist"]), Validation::ARTIST_LENGTH))
            {
                $returnMessage["artist"] = "Artist name must be ".Validation::ARTIST_LENGTH." characters or under!";
            }

            //check target tempo
            if (!$this->validateInt($song["Target"]))
            {
                $returnMessage["target"] = "Invalid target tempo";
            }
            else
            {
                if (!$this->validateTempo($song["Target"]))
                {
                    $returnMessage["bpmRangeTarget"] = "Target tempo is out of range!";
                }
            }

            //if exists, check current tempo
            if (isset($song["Current"]))
            {
                if (!$this->validateInt($song["Current"]))
                {
                    $returnMessage["current"] = "Invalid current tempo";
                }
                else
                {
                    if (!$this->validateTempo($song["Current"]))
                    {
                        $returnMessage["bpmRangeCurrent"] = "Current tempo is out of range!";
                    }
                }
            }

            if (count($returnMessage) == 0)
            {
                return null;
            }
            else
            {
                return json_encode($returnMessage, JSON_OBJECT_AS_ARRAY);
            }
        }
    }

?>