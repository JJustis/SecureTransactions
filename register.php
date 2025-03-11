<?php
/**
 * API Registration Endpoint
 * 
 * Handles user registration
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
if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

try {
    // Create the user account
    $userAccount = new UserAccount();
    $userId = $userAccount->createUser(
        $data['username'],
        $data['email'],
        $data['password'],
        $data['profile'] ?? []
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user_id' => $userId
    ]);
} catch (Exception $e) {
    // Handle duplicate user errors specifically
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'Username or email already exists'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
