<?php
/**
 * API Bank Note Receipt Endpoint
 * 
 * Generates a receipt for a bank note
 */

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
    <?php
/**
 * API Bank Note Receipt Endpoint
 * 
 * Generates a receipt for a bank note
 */

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
if (!isset($data['note_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing note ID'
    ]);
    exit;
}

try {
    // Generate receipt for the bank note
    $bankNote = new BankNote();
    $receipt = $bankNote->generateReceipt($data['note_id'], $userId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Receipt generated successfully',
        'receipt' => $receipt
    ]);
} catch (Exception $e) {
    // Handle specific errors
    if (strpos($e->getMessage(), 'Unauthorized') !== false) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'You are not authorized to view this receipt'
        ]);
    } elseif (strpos($e->getMessage(), 'not found') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'Bank note not found'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}