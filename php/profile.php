<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

require 'redis_session.php';
require 'db_mysql.php';
require 'db_mongo.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get token from Authorization header safely
$auth_header = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $requestHeaders = apache_request_headers();
    if (isset($requestHeaders['Authorization'])) {
        $auth_header = $requestHeaders['Authorization'];
    }
}

if (empty($auth_header) || strpos($auth_header, 'Bearer ') !== 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Missing or invalid token.']);
    exit;
}

$token = substr($auth_header, 7); // Remove 'Bearer '
$email = getSessionUser($redis, $token);

if (!$email) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired session.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Return profile details
    
    // 1. Get Name/Email from MySQL
    $stmt = $mysql_conn->prepare("SELECT name, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user_mysql = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 2. Get additional details from MongoDB
    $filter = ['email' => $email];
    $options = [];
    $query = new MongoDB\Driver\Query($filter, $options);
    $cursor = $mongo_conn->executeQuery("$mongo_db.$mongo_collection", $query);
    
    $user_mongo = current($cursor->toArray());
    
    $profile_data = [
        'name' => $user_mysql['name'],
        'email' => $user_mysql['email'],
        'age' => $user_mongo ? $user_mongo->age : '',
        'dob' => $user_mongo ? $user_mongo->dob : '',
        'contact' => $user_mongo ? $user_mongo->contact : ''
    ];
    
    echo json_encode(['status' => 'success', 'data' => $profile_data]);

} elseif ($method === 'POST') {
    // Update profile details in MongoDB
    $age = isset($_POST['age']) ? trim($_POST['age']) : '';
    $dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
    $contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $filter = ['email' => $email];
    $update = [
        '$set' => [
            'age' => $age,
            'dob' => $dob,
            'contact' => $contact
        ]
    ];
    $options = ['upsert' => true];
    
    $bulk->update($filter, $update, $options);
    
    try {
        $result = $mongo_conn->executeBulkWrite("$mongo_db.$mongo_collection", $bulk);
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
}
?>
