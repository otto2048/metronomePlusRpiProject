<?php
    //include validation
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/controller/Validation.php");

    //include model class
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/model/UserModel.php");
    
    
    Class LoginController
    {
        private $validationObj;
        private $userModelObj;

        public function __construct()
        {
            //init validation object
            $this->validationObj = new Validation();

            //init user object
            $this->userModelObj = new UserModel();

            //begin the session
            session_start();
        }

        //function to handle user login
        //return void
        public function loginUser()
        {
            //user input
            $usernameInput=$_POST['uname'];
            $passwordInput=$_POST['password'];

            //sanitize user input
            $username = $this->validationObj->cleanInput($usernameInput);

            //check username is valid
            if (!$this->validationObj->validateString($username, Validation::USERNAME_LENGTH))
            {
                //end session
                session_destroy();

                //return to the login page
                header("location: /cmp408/metronome-plus-rpi-web/view/login.php"."?error_message=login_failed");

                return;
            }
            
            //variable to hold user data
            $userData = null;

            $authenticated = $this->userModelObj->loginUser($userData, $username, $passwordInput);

            if ($authenticated)
            {
                //set up session variables
                $userData = json_decode($userData, JSON_OBJECT_AS_ARRAY);
                $_SESSION["Uname"] = $userData["Uname"];
                $_SESSION["PiClientId"] = $userData["PiClientId"];

                //send to metronome page         
                header("location: /cmp408/metronome-plus-rpi-web/view/index.php");     
            }
            else
            {
                //end session
                session_destroy();

                //return to login page
                header("location: /cmp408/metronome-plus-rpi-web/view/login.php?error_message=login_failed");
            }
        }
    }
?>