<?php
session_start(); //Optional if you know how to start your session outside this file

function loadClasses($class) {
    $path = "./classes/";
    require_once("{$path}{$class}.php");
}

spl_autoload_register('loadClasses');

//Instantiating the CRUD Class
$crud = new CRUD($server, $db_user, $db_password, $db);

//Making Connection to Database
$conn = $crud->connect();

/**
 * Using CRUDify add() method to add data to databse
 * $crud->add($conn, 'db_table', $_POST, $format, false)  
 */
/**
 * Using CRUDify add() method to add data to databse
 * $crud->update($conn, 'db_table', $_POST, $format, false)  
 */

//Possible Update

// try {
//     return $result;
// } catch(Exception $e) {
//     echo "Error! : {$e->getMeesage()}";
// }

 try {
     $get_users = $crud->get($conn, "SELECT * FROM tbl_users WHERE id={$_GET['id']}");
 } catch(Exception $e) {
     echo "Error!: {$e->getMessage()}";
 }