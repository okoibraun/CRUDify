<?php

class User extends CRUD
{
    // /**
    //  * Propertiees
    //  */
    // private $username;
    // private $password;

    // function __construct($username, $password) {
    //     $this->username = $username;
    //     $this->password = $password;
    // }

    /**
    * Performs User login
    *
    * @param $query
    * @return array if true and bool(false) if false
    */
    public function login($conn, $query) {

        $result = $conn->query($query) or die($conn->error);

        $data = $result->fetch_assoc();

        $rows = $result->num_rows;
        
        if($rows != 0) {
            foreach($data as $key=>$value) {
                $_SESSION[$key] = $value;
            }
            
            return $data;
        } else {
            return false;
        }
    }

    /**
    * Log's Out the current user that is logged in
    * @param string the action to perform or carry out after a successful logout
    * @param string check if the action to be perform to know if it equal to logout
    */
    public function logout($action, $check) {
        
        if(isset($action) && $action == "logout" && isset($check) && $check == "true") {
            for($i=0; $i<=count($_SESSION); $i++) {
                $_SESSION[$i] = "";
                unset($_SESSION[$i]);
            }

            session_destroy();

            return true;
        }
    }
    
    //Check if session is no set
    function logged_in_not($key, $url) {
        if(!isset($key)) {
            $this->redirect("{$url}");
        }
    }

    //Check if session is set
    function logged_in($key, $url) {
        if(isset($key)) {
            $this->redirect("{$url}");
        }
    }
}