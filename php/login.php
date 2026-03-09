<?php
header('Content-Type: application/json');

require 'db_mysql.php';
require 'redis_session.php'; // Includes Redis functions

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit;
}

try {
    // 1. Fetch user by email via MySQL Prepared Statement
    $stmt = $mysql_conn->prepare("SELECT id, name, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        exit;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Verify password
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        exit;
    }

    // 3. Generate session token and store in Redis
    $session_token = setSessionToken($redis, $user['email']);

    // 4. Return success with token for localStorage
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'token' => $session_token,
        'user' => [
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}
?>
