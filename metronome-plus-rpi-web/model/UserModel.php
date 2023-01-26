<?php
    require_once("Model.php");

    Class UserModel extends Model {
        //create user
        public function createUser($Uname, $password, $PiClientId)
        {
            $userTable = "Users";

            //hash password
            $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

            $data = new \stdClass();

            $data -> Uname = $Uname;
            $data -> Password = $passwordHashed;
            $data -> PiClientId = $PiClientId;

            $jsonData = json_encode($data, JSON_OBJECT_AS_ARRAY);

            return parent::put($userTable, $this->connection->getMarshaler()->marshalJson($jsonData));
        }

        //get user
        public function getUser($Uname)
        {
            $userTable = "Users";

            $key = new \stdClass();
            $key -> Uname = $Uname;

            $jsonKey = json_encode($key, JSON_OBJECT_AS_ARRAY);

            return parent::read($userTable, $this->connection->getMarshaler()->marshalJson($jsonKey));
        }

        //login user
        public function loginUser(&$userData, $Uname, $password)
        {
            $userData = null;

            //get user details from username
            $resultJSON = $this->getUser($Uname);

            //check if user exists
            if (!$resultJSON)
            {
                return false;
            }

            $result = json_decode($resultJSON, JSON_OBJECT_AS_ARRAY);

            //verify password
            if (password_verify($password, $result["Password"]))
            {
                $userData = $resultJSON;
                return true;
            }

            return false;
        }
    }
?>