<?php

class User extends Paysoft
{
    /**
    * Performs User loin
    *
    * @param $query
    * @return array if true and bool(false) if false
    */
    public function login($query) {
        global $conn;

        $result = $conn->query($query) or die($conn->error);

        $row = $result->fetch_assoc();

        $returned = $result->num_rows;
        
        if($returned != 0) {
            foreach($row as $key=>$value) {
                $_SESSION[$key] = $value;
            }
            
            return $row;
        } else {
            return false;
        }
    }

    /**
    * Log's Out a User that is logged in
    * @param string 
    * @param string
    */
    public function logout($action, $check) {

        if(isset($action) && $action == "logout" && isset($check) && $check == "true") {
            for($i=0; $i<=count($_SESSION); $i++) {
                $_SESSION[$i] = "";
                unset($_SESSION[$i]);
            }

            $destroy = session_destroy();

            return $destroy;
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