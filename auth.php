<?php
/**
 * API Authentication Endpoint
 * 
 * Handles user login and returns session information
 */

// Include main implementation
require_once '../server-implementation.php';

// Process only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed'
    ]);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate request data
if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing username or password'
    ]);
    exit;
}

try {
    // Authenticate the user
    $userAccount = new UserAccount();
    $session = $userAccount->authenticate($data['username'], $data['password']);
    
    if ($session) {
        echo json_encode([
            'success' => true,
            'message' => 'Authentication successful',
            'session' => $session['session_id'],
            'expiry' => $session['expiry'],
            'user_id' => $session['user_id']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Authentication failed'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
