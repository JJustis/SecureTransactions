<?php
/**
 * API Bank Note Deposit Endpoint
 * 
 * Deposits (redeems) a bank note for the authenticated user
 */
ob_start();
// Include main implementation
require_once '../../server-implementation.php';

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
if (!isset($data['note_identifier'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing note identifier'
    ]);
    exit;
}

try {
    // Deposit the bank note
    $bankNote = new BankNote();
    $result = $bankNote->depositBankNote($data['note_identifier'], $userId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Bank note deposited successfully',
        'bank_note' => $result
    ]);
} catch (Exception $e) {
    // Handle specific errors
    if (strpos($e->getMessage(), 'expired') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'This bank note has expired and cannot be deposited'
        ]);
    } elseif (strpos($e->getMessage(), 'redeemed') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'This bank note has already been redeemed'
        ]);
    } elseif (strpos($e->getMessage(), 'not found') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'Bank note not found or invalid'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
}}