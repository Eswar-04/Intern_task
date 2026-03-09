<?php
// MongoDB connection configuration
try {
    // Assuming MongoDB is running locally on default port
    // Using the native PHP MongoDB Driver Manager
    $mongo_conn = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    
    $mongo_db = "intern_profiles"; // The database name
    $mongo_collection = "users";   // Collection name

} catch (MongoDB\Driver\Exception\Exception $e) {
    die(json_encode([
        'status' => 'error',
        'message' => 'MongoDB Connection failed: ' . $e->getMessage()
    ]));
}
?>
