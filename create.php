<?php
/**
 * API Bank Note Create Endpoint
 * 
 * Creates a new bank note for the authenticated user
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
if (!isset($data['amount']) || !isset($data['note_id']) || !isset($data['serial_number'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

try {
    // Create the bank note
    $bankNote = new BankNote();
    $note = $bankNote->createBankNote(
        $data['note_id'],
        $data['serial_number'],
        $data['amount'],
        $data['expiry_days'] ?? 30,
        $userId
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Bank note created successfully',
        'note_id' => $note['note_id'],
        'serial' => $note['serial'],
        'amount' => $note['amount'],
        'issuer' => $note['issuer'],
        'issuer_id' => $note['issuer_id'],
        'issued_at' => $note['issued_at'],
        'expires_at' => $note['expires_at'],
        'status' => $note['status'],
        'signature' => $note['signature'],
        'transaction_id' => $note['transaction_id']
    ]);
} catch (Exception $e) {
    // Handle insufficient funds specifically
    if (strpos($e->getMessage(), 'Insufficient funds') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient funds to create bank note'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
