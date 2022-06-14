<?php
/**
* CRUDify - Create CRUD operations easily, simply and faster with PHP
*
* @author Braun Okoi Boniface <ldbraun@live.com>
* @license MIT <https://opensource.org/licenses/MIT>
*/

class CRUD 
{
    /**
     * Properties
     * 
     */
    // private $host;
    // private $username;
    // private $password;
    // private $database;

    /**
     * Initialize the base class
     */
    function __construct($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }
    
    /**
     * Connect Method
     * 
     * @return resource Database connection resource
     */
    public function connect() {
        return new mysqli($this->host, $this->username, $this->password, $this->database);
    }
    
    /**
     * Redirection
     */
    public function redirect($location = NULL) {
        if($location != NULL) {
            header("Location: {$location}");
            exit;
        } else {
            header("Location: {$_SERVER['HTTP_REFERER']}");
        }
    }
    
    
    //CRUD Operations
    
    /**
    *@return $result 
    *@return $error
    */
    function list_all($conn, $table) {
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
    function get($conn, $query) {
        $sql = $query;
        $result = $conn->query($sql);
        
        if(!$result) {
            throw new Exception($conn->error);
        }
        
        return $result;
    }
    
    //Delete Functions
    function delete($conn, $table, $clause, $recid, $goto) {        
        if((isset($table, $recid, $clause)) && ($table != "" && $recid != "")) {
            $sql = "DELETE FROM {$table} WHERE {$clause} IN({$recid})";
            $execute = $conn->query($sql) or die($conn->connect_error());
            
            if($execute) {
                $this->redirect("{$goto}");
            } else {
                $this->redirect("{$goto}");
            }
        } else {
            $this->redirect("{$goto}");
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
        
        $post_data = [
            "tcols" => [],
            "tvals" => [],
            "qmarks" => [],
            "data_type" => []
        ];

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
        $params = [$data_type];
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
            $error = $stmt->error;
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
    public function update($conn, $table, array $columns, array $condition, $formats=[], $data_type=[], $avail_btn=true) {
        $error = "";
        
        if(!empty($formats) && count($formats) > 0) {
            foreach($formats as $fkey=>$fval) {
                $columns[$fkey] = $fval;
            }
        }
        
        if($avail_btn === true) {
            array_pop($columns);
        }
        
        $post_data = [
            "tsets" => [],
            "tvals" => [],
            "data_type" => []
        ];

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
        $params = [$data_type];
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
    * Uploads Multiple File
    *
    * @param array $fieldname The files to be uploaded to the file system
    * @param string $uploadto The path the files will be uploaded to
    * @param array $filetype Upload file type
    * @return array uploaded file(s) properties in an associative array
    */
    function upload($fieldname, $uploadto, array $filetype=[]) {

        $files=[
            'name'=>[],
            'type'=>[],
            'tmp_name'=>[]
        ]; //Literal Array definition
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
                    array_push($files['name'], date('Ymd') . time() . "_{$fileIndex}.{$ext}");
                    array_push($files['type'], $fdata['type'][$i]);
                    array_push($files['tmp_name'], $fdata['tmp_name'][$i]);
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
}