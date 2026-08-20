<?php
    ob_start();
    session_start();
    date_default_timezone_set('Asia/Manila');

    $DB_HOST = 'localhost';
    $DB_USER = 'root';
    $DB_PASS = '';
    $DB_NAME = 'parku_db';

    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME); 

    if ($conn->connect_error) {
        die ("Failed to connect to MySQL: " . $conn->connect_error);
    }
?>