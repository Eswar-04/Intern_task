<?php
// Redis configuration
try {
    // Assuming the native PHP Redis extension (PECL) is installed
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);

} catch (Exception $e) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Redis Connection failed: ' . $e->getMessage()
    ]));
}

/**
 * Generates a session token and stores it in Redis.
 * Returns the token.
 */
function setSessionToken($redis, $userEmail) {
    $token = bin2hex(random_bytes(32));
    // Store token mapping to user email. Expires in 24 hours (86400 seconds)
    $redis->setex("session_token:" . $token, 86400, $userEmail);
    return $token;
}

/**
 * Gets the user email associated with a session token.
 * Returns false if the token doesn't exist or is expired.
 */
function getSessionUser($redis, $token) {
    if (empty($token)) {
        return false;
    }
    return $redis->get("session_token:" . $token);
}

/**
 * Deletes a session token from Redis (Logout).
 */
function deleteSessionToken($redis, $token) {
    if (empty($token)) {
        return false;
    }
    return $redis->del("session_token:" . $token);
}
?>
