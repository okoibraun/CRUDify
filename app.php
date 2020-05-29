<?php
session_start(); //Optional if you know how to start your session outside this file

function loadClasses($class) {
    $path = "./classes/";
    require_once("{$path}{$class}.php");
}

spl_autoload_register('loadClasses');

//Instantiating the academy App Class
$crud = new CRUD;

//Making Connection to Database
$conn = $crud->connect($server, $db_user, $db_password, $db);

/**
 * Using CRUDify add() method to add data to databse
 * $crud->add($conn, 'db_table', $_POST, $format, false)  
 */
/**
 * Using CRUDify add() method to add data to databse
 * $crud->update($conn, 'db_table', $_POST, $format, false)  
 */