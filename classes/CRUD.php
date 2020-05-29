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
    function get($query) {
        global $conn;
        $sql = $query;
        $result = $conn->query($sql);
        
        if(!$result) {
            die($conn->error);
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
    * Insert's new record to database table from form or any key=>value pair record using SQL prepared statement
    *
    *@param string $table The database table to insert the record to
    *@param array $post_fields http method(post, get) or key=>value pair
    *@param array $formats Additional form field format e.g: setting encrypting password field like array("password"=>md5('password'))
    *@param boolean $avail_btn Checks if submit button in the form has a name and is included in the post parameters
    */
    function add($conn, $table, array $columns, $formats=[], $data_type=[], $avail_btn=true) {
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
            "tcols" => [],
            "tvals" => [],
            "qmarks" => [],
            "data_type" => []
        );

        foreach($columns as $key=>$val) {
            if((!empty($data_type) && count($data_type) > 0) && isset($data_type[$key])) {
                array_push($post_data['tcols'], "`{$key}`");
                array_push($post_data['tvals'], "{$val}");
                array_push($post_data['qmarks'], "?");
                array_push($post_data['data_type'], "{$data_type[$key]}");
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
    public function update($table, array $columns, array $condition, $formats=[], $data_type=[], $avail_btn=true) {
        global $conn;
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
            "tsets" => [],
            "tvals" => [],
            "data_type" => []
        );

        foreach($columns as $key=>$val) {
            if((!empty($data_type) && count($data_type) > 0) && isset($data_type[$key])) {
                array_push($post_data['tsets'], "`{$key}`=?");
                array_push($post_data['tvals'], "{$val}");
                array_push($post_data['data_type'], "{$data_type[$key]}");
            } else {
                array_push($post_data['tsets'], "`{$key}`=?");
                array_push($post_data['tvals'], "{$val}");
                array_push($post_data['data_type'], "s");
            }
        }

        //Conversions
        $tsets = implode(", ", $post_data['tsets']);
        //$qmarks = implode(", ", $post_data['qmarks']);
        $data_type = implode("", $post_data['data_type']);
        $params = array($data_type);
        $k=0;
        foreach($post_data['tsets'] as $f=>$v) {
            $k++;
            $params[$k] = & $post_data['tvals'][$f];
            
        }

        $stmt = $conn->prepare("UPDATE {$table} SET {$tsets} WHERE {$condition['key']}='{$condition['val']}'");
        //$stmt->bind_param($pbind, $pcols);
        call_user_func_array(array($stmt, "bind_param"), $params);
        
        if($stmt->execute()) {
            return true;
        }
        
        $stmt->close();
        $conn->close();
    }
    
    /**
    * Performs User login
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
    * Uploads Multiple File
    *
    * @param array $fieldname The files to be uploaded to the file system
    * @param string $uploadto The path the files will be uploaded to
    * @param array $filetype Upload file type
    * @return array uploaded file(s) properties in an associative array
    */
    function upload($fieldname, $uploadto, array $filetype=[]) {

        $files=array();
        $fdata=$_FILES[$fieldname];
        if(is_array($fdata['name'])) {
            
            $filename = $fdata['name'];
            
            $fileIndex = 0;
            for($i=0; $i<count($fdata['name']); ++$i) {
                $ext = pathinfo($fdata['name'][$i], PATHINFO_EXTENSION);
                if(count($filetype) > 0 && !in_array($ext, $filetype)) {
                    $this->redirect("{$_SERVER['HTTP_REFERER']}?filetype=notAllowed");
                } else {
                    $fileIndex++;
                    $files[]=array(
                        'name'=>date('Ymd' . time()) . "_{$fileIndex}.{$ext}",
                        'type'=>$fdata['type'][$i],
                        'tmp_name'=>$fdata['tmp_name'][$i]
                    );
                }
            }
        } else {
            $files[]=$fdata;
        }

        foreach($files as $file) {
            move_uploaded_file($file['tmp_name'], $uploadto . $file['name']);

        }

        return $files;
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