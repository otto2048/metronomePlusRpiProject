<?php
    require_once("Model.php");
    
    Class SongModel extends Model {

        //get songs for user
        public function getSongs($UName)
        {
            $songTable = "Songs";

            $key = [
                'Key' => [
                    'Uname' => [
                        'S' => $UName,
                    ],
                ],
            ];

            return parent::query($songTable, $key);
        }

        //get song for user
        public function getSong($UName, $songTitle)
        {
            $songTable = "Songs";
            $key = new \stdClass();
            $key -> Uname = $UName;
            $key -> SongTitle = $songTitle;

            $jsonKey = json_encode($key, JSON_OBJECT_AS_ARRAY);

            return parent::read($songTable, $this->connection->getMarshaler()->marshalJson($jsonKey));
        }

        //create song
        public function putSong($jsonData)
        {
            $songTable = "Songs";

            return parent::put($songTable, $this->connection->getMarshaler()->marshalJson($jsonData));
        }

        //update song
        public function updateSong($UName, $SongTitle, $jsonData)
        {
            $songTable = "Songs";

            $data = json_decode($jsonData, JSON_OBJECT_AS_ARRAY);

            $key = new \stdClass();
            $key -> Uname = $UName;
            $key -> SongTitle = $SongTitle;

            $jsonKey = json_encode($key, JSON_OBJECT_AS_ARRAY);

            $expressionAttributeValues = [];
            $expressionAttributeNames = [];
            $keyConditionExpression = "";
            $index = 1;

            $updateExpression = "set ";

            foreach ($data as $attribute => $value)
            {

                $updateExpression .= "#".$attribute."=:".$attribute.",";

                $name = "#".$attribute;
                $expressionAttributeNames[$name] = $attribute;

                $name = ":".$attribute;
                $attrib = new \stdClass();
                $attrib -> value = $value;

                $expressionAttributeValues[$name] = array(array_keys($this->connection->getMarshaler()->marshalJson(json_encode($attrib, JSON_OBJECT_AS_ARRAY))["value"])[0] => $value);

                
            }

            $updateExpression = substr($updateExpression, 0, -1);

            return parent::update($songTable, $this->connection->getMarshaler()->marshalJson($jsonKey), $expressionAttributeNames, $expressionAttributeValues, $updateExpression);

        }

        //delete song
        public function deleteSong($UName, $songTitle)
        {
            $songTable = "Songs";
            $key = new \stdClass();
            $key -> Uname = $UName;
            $key -> SongTitle = $songTitle;

            $jsonKey = json_encode($key, JSON_OBJECT_AS_ARRAY);

            return parent::delete($songTable, $this->connection->getMarshaler()->marshalJson($jsonKey));
        }


        //add tempo to song
        public function addTempo($UName, $SongTitle, $tempo)
        {
            $songTable = "Songs";

            $key = new \stdClass();
            $key -> Uname = $UName;
            $key -> SongTitle = $SongTitle;

            $jsonKey = json_encode($key, JSON_OBJECT_AS_ARRAY);

            $expressionAttributeValues = [];

            //SOURCE: Stack Overflow, 2020
            //ACCESSED FROM: https://stackoverflow.com/questions/33688596/dynamodb-add-new-map-to-list
            $updateExpression = "set #i = list_append(if_not_exists(#i, :empty), :i), #c = :c";

            $value = array("Value" => $tempo, "Created" => time());
            $valueJSON =  json_encode($value, JSON_OBJECT_AS_ARRAY);

            $expressionAttributeValues[":i"] = array("L" => array((array("M" => $this->connection->getMarshaler()->marshalJson($valueJSON)))));
            $expressionAttributeValues[":empty"] = array("L" => array());
            $expressionAttributeValues[":c"] = array("S" => $tempo);

            $expressionAttributeNames["#i"] = "Tempos";
            $expressionAttributeNames["#c"] = "Current";

            return parent::update($songTable, $this->connection->getMarshaler()->marshalJson($jsonKey), $expressionAttributeNames, $expressionAttributeValues, $updateExpression);

        }
    }
?>