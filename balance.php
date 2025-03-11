<?php
/**
 * API Balance Endpoint
 * 
 * Handles balance updates for the authenticated user
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

// Authenticate the request
$headers = getallheaders();
$userId = null;

if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
        
        // Verify token
        $db = (new Database())->getConnection();
        $stmt = $db->prepare(
            "SELECT user_id FROM sessions 
             WHERE session_id = :token AND expiry > :now"
        );
        
        $stmt->execute([
            ':token' => $token, 
            ':now' => time()
        ]);
        
        $session = $stmt->fetch();
        
        if ($session) {
            $userId = $session['user_id'];
        }
    }
}

if (!$userId) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate request data
if (!isset($data['amount']) || !isset($data['transaction_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields (amount and transaction_id)'
    ]);
    exit;
}

try {
    // Update the balance
    $userAccount = new UserAccount();
    $result = $userAccount->updateBalance(
        $userId,
        $data['amount'],
        $data['transaction_id'],
        $data['transaction_type'] ?? 'adjustment',
        $data['reference'] ?? ''
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Balance updated',
        'new_balance' => $result['new_balance'],
        'transaction_id' => $result['transaction_id']
    ]);
} catch (Exception $e) {
    // Handle insufficient funds specifically
    if (strpos($e->getMessage(), 'Insufficient funds') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient funds for this transaction'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
