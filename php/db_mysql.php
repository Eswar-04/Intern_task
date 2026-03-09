<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root'; // Change if necessary
$db_pass = '';     // Change if necessary
$db_name = 'intern_auth';

// Create connection
try {
    $mysql_conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    // Set the PDO error mode to exception
    $mysql_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully"; 
} catch(PDOException $e) {
    die(json_encode([
        'status' => 'error',
        'message' => 'MySQL Connection failed: ' . $e->getMessage()
    ]));
}
?>
