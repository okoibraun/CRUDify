<?php
/**
* CRUDify - Create CRUD operations easily, simply and faster with PHP
*
* @author Braun Okoi Boniface <ldbraun@live.com>
* @license MIT <https://opensource.org/licenses/MIT>
*/

class CRUD 
{
    function __construct() {
        
    }
    
    public function connect($host, $username, $password, $database) {
        $conn = new mysqli($host, $username, $password, $database);
        
        return $conn;
    }
    
    //Redirection function
    public function redirect($location = NULL) {
        if($location != NULL) {
            header("Location: {$location}");
            exit;
        } else {
            header("Location: index.php");
            exit;
        }
    }
    //End Redirection function
    
    
    //CRUD Operations
    
    /**
    *@return $result 
    *@return $error
    */
    
    function list_all($table) {
        global $conn;
        $sql = "SELECT * FROM `{$table}`";
        $result = $conn->query($sql);
        
        if(!$result) {
            throw new Exception($conn->error);
        }
        
        return $result;
    }
    
    /**
    *
    *
    */
    function get_rec($query) {
        global $conn;
        $sql = $query;
        $result = $conn->query($sql);
        
        if(!$result) {
            throw new Exception($conn->error);
        }
        
        return $result;
    }
    
    //Delete Functions
    function delete($table, $clause, $recid, $goto) {
        global $conn;
        
        if((isset($table, $recid, $clause)) && ($table != "" && $recid != "")) {
            $sql = "DELETE FROM {$table} WHERE {$clause} IN({$recid})";
            $execute = $conn->query($sql) or die($conn->connect_error());
            
            if($execute) {
                $this->redirect("{$goto}?s=1");
            } else {
                $this->redirect("{$goto}?s=0");
            }
        } else {
            $this->redirect("{$goto}?s=0");
        }
    }
    
    /**
    *
    * Insert's new record to database table from form or any key=>value pair record
    *
    *@param string $table The database table to insert the record to
    *@param array $post_fields http method(post, get) or key=>value pair
    *@param array $formats Additional form field format e.g: setting encrypting password field like array("password"=>md5('password'))
    *@param boolean $avail_btn Checks if submit button in the form has a name and is included in the post parameters
    */
    function add_rec($table, array $post_fields, $formats=array(), $avail_btn=true) {
        global $conn;
        $error = "";
        
        if(!empty($formats) && count($formats) > 0) {
            foreach($formats as $fkey=>$fval) {
                $post_fields[$fkey] = $fval;
            }
        }
        
        $fields = array();
        $vals = array();

        foreach($post_fields as $field=>$val) {
            array_push($fields, "`{$field}`");
            array_push($vals, "'{$conn->real_escape_string($val)}'");
        }
        
        if($avail_btn === true) {
            array_pop($fields);
            array_pop($vals);
        }

        //Conversions
        $cols = implode(", ", $fields);
        $data = implode(", ", $vals);

        $sql = "INSERT INTO {$table}({$cols}) VALUES ({$data})";
        $result = $conn->query($sql) or die($conn->error);
        
        return $result;
    }
    
    /**
    *
    * Insert's new record to database table from form or any key=>value pair record using SQL prepared statement
    *
    *@param string $table The database table to insert the record to
    *@param array $post_fields http method(post, get) or key=>value pair
    *@param array $formats Additional form field format e.g: setting encrypting password field like array("password"=>md5('password'))
    *@param boolean $avail_btn Checks if submit button in the form has a name and is included in the post parameters
    */
    function add_prepared_rec($conn, $table, array $columns, $formats=array(), $avail_btn=true) {
        $error = "";
        
        if(!empty($formats) && count($formats) > 0) {
            foreach($formats as $fkey=>$fval) {
                $columns[$fkey] = $fval;
            }
        }
        
        if($avail_btn === true) {
            array_pop($columns);
        }
        
        $post_data = array(
            "tcols" => array(),
            "tvals" => array(),
            "qmarks" => array(),
            "data_type" => array()
        );

        foreach($columns as $key=>$val) {
            if("org_id" === $key) {
                array_push($post_data['tcols'], "`{$key}`");
                array_push($post_data['tvals'], "{$val}");
                array_push($post_data['qmarks'], "?");
                array_push($post_data['data_type'], "i");
            } else {
                array_push($post_data['tcols'], "`{$key}`");
                array_push($post_data['tvals'], "{$val}");
                array_push($post_data['qmarks'], "?");
                array_push($post_data['data_type'], "s");
            }
        }

        //Conversions
        $tcols = implode(", ", $post_data['tcols']);
        $qmarks = implode(", ", $post_data['qmarks']);
        $data_type = implode("", $post_data['data_type']);
        $params = array($data_type);
        $k=0;
        foreach($post_data['qmarks'] as $f=>$v) {
            $k++;
            $params[$k] = & $post_data['tvals'][$f];
            
        }
        
        $stmt = $conn->prepare("INSERT INTO {$table}({$tcols}) VALUES ({$qmarks})");
        //$stmt->bind_param($pbind, $params) or die($stmt->error);
        call_user_func_array(array($stmt, "bind_param"), $params);
        
        if($stmt->execute()) {
            return true;
        } else {
            $error = $php_errormsg || $stmt->error;
            return $error;
        }
        
        $stmt->close();
        $conn->close();
    }
    
    /**
    *
    * Updates existing database table record(s) from form or any key=>value pair record
    *
    *@param string $table The database table to be updated
    *@param array $post_fields http method(post, get) or key=>value pair
    *@param string $clause Clause to be used for update
    *@param array $formats Additional form field format e.g: setting encrypting password field like array("password"=>md5('password'))
    *@param boolean $avail_btn Checks if submit button in the form has a name and is included in the post parameters
    */
    public function upd_rec_prepared($table, array $post_fields, array $clause, $formats=array(), $avail_btn=true) {
        global $conn;
        $error = "";
        
        if(!empty($formats) && count($formats) > 0) {
            foreach($formats as $fkey=>$fval) {
                $post_fields[$fkey] = $fval;
            }
        }
        
        $post_data = array(
            "psets" => array(),
            "pvals" => array(),
            "pb" => array(),
            "vars"=>array()
        );

        foreach($post_fields as $field=>$val) {
            array_push($post_data['psets'], "`{$field}`=?");
            array_push($post_data['pvals'], parse_str($val));
            array_push($post_data['pb'], "s");
            array_push($post_data['vars'], "{$field}=>{$val}");
        }
        
        if($avail_btn === true) {
            array_pop($post_data['psets']);
            array_pop($post_data['pvals']);
            array_pop($post_data['pb']);
        }

        //Conversions
        $pcols = implode(", ", $post_data['psets']);
        $pdata = implode(", ", $post_data['pvals']);
        $pbind = implode("", $post_data['pb']);

        $stmt = $conn->prepare("UPDATE {$table} SET {$pcols} WHERE {$clause['clause']}='{$clause['value']}'");
        $stmt->bind_param($pbind, $pcols);
        
        if($stmt->execute()) {
            return "Done";
        }
        
        $stmt->close();
        $conn->close();
//        return "UPDATE {$table} SET {$pcols} WHERE {$clause['clause']}='{$clause['value']}'";
    }
    
    /**
    *
    * Updates existing database table record(s) from form or any key=>value pair record
    *
    *@param string $table The database table to be updated
    *@param array $post_fields http method(post, get) or key=>value pair
    *@param string $clause Clause to be used for update
    *@param array $formats Additional form field format e.g: setting encrypting password field like array("password"=>md5('password'))
    *@param boolean $avail_btn Checks if submit button in the form has a name and is included in the post parameters
    */
    public function upd_rec($table, array $post_fields, array $clause, array $formats=null, $avail_btn=true) {
        global $conn;
        $error = "";
        
        if(!empty($formats) && count($formats) > 0) {
            foreach($formats as $fkey=>$fval) {
                $post_fields[$fkey] = $fval;
            }
        }
        
        $post_data = array(
            "psets" => array()
        );

        foreach($post_fields as $field=>$val) {
            array_push($post_data['psets'], "`{$field}`='" .$conn->real_escape_string($val) . "'");
        }
        
        if($avail_btn === true) {
            array_pop($post_data['psets']);
        }

        //Conversions
        $pcols = implode(", ", $post_data['psets']);

        $stmt = "UPDATE {$table} SET {$pcols} WHERE {$clause['clause']}='{$clause['value']}'";
        //die($stmt);
        $result = $conn->query($stmt);
        
        if($result) {
            return $result;
        } else {
            die($conn->error);
        }
    }
    
    /**
    * Performs User loin
    *
    * @param $query 
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
            return "Error";
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