<?php
/**
 * API Bank Note Import Endpoint
 * 
 * Imports a bank note from JSON or file upload
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

// Check if this is a file upload or JSON input
$noteData = null;
$source = 'json';

if (isset($_FILES['note_file']) && $_FILES['note_file']['error'] == UPLOAD_ERR_OK) {
    // This is a file upload
    $source = 'file';
    $fileContent = file_get_contents($_FILES['note_file']['tmp_name']);
    $noteData = json_decode($fileContent, true);
} else {
    // This is JSON input
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['note_json'])) {
        $noteData = json_decode($data['note_json'], true);
    }
}

// Validate the note data
if (!$noteData || !isset($noteData['securebank_note'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid bank note format'
    ]);
    exit;
}

$note = $noteData['securebank_note'];

// Validate required fields
$requiredFields = ['note_id', 'serial', 'amount', 'issuer', 'issuer_id', 'issued_at', 'expires_at', 'signature'];
foreach ($requiredFields as $field) {
    if (!isset($note[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: {$field}"
        ]);
        exit;
    }
}

try {
    // Verify the bank note signature
    $signatureData = [
        'note_id' => $note['note_id'],
        'serial' => $note['serial'],
        'amount' => $note['amount'],
        'issuer' => $note['issuer'],
        'issuer_id' => $note['issuer_id'],
        'issued_at' => $note['issued_at'],
        'expires_at' => $note['expires_at']
    ];
    
    if (!Security::verifySignature($signatureData, $note['signature'])) {
        throw new Exception("Invalid bank note signature");
    }
    
    // Check if the note has expired
    if (strtotime($note['expires_at']) < time()) {
        throw new Exception("Bank note has expired");
    }
    
    // Verify the note doesn't already exist in our system
    $bankNote = new BankNote();
    
    try {
        $existingNote = $bankNote->getBankNote($note['note_id']);
        // If we get here, the note already exists
        if ($existingNote['status'] !== 'active') {
            throw new Exception("Bank note has already been " . strtolower($existingNote['status']));
        }
        
        // The note exists and is active - tell the user
        echo json_encode([
            'success' => true,
            'message' => 'Bank note verified and ready for deposit',
            'bank_note' => $existingNote,
            'action' => 'deposit'
        ]);
        exit;
    } catch (Exception $e) {
        // Note doesn't exist, which is what we want for import
        if (strpos($e->getMessage(), 'not found') === false) {
            // Some other error
            throw $e;
        }
    }
    
    // Store the bank note in our system for later deposit
    $stmt = $db->prepare(
        "INSERT INTO bank_notes (
            note_id, serial_number, amount, issuer_id, 
            status, signature, issued_at, expires_at
        ) VALUES (
            :note_id, :serial, :amount, :issuer_id,
            'active', :signature, :issued_at, :expires_at
        )"
    );
    
    $stmt->execute([
        ':note_id' => $note['note_id'],
        ':serial' => $note['serial'],
        ':amount' => $note['amount'],
        ':issuer_id' => $note['issuer_id'],
        ':signature' => $note['signature'],
        ':issued_at' => strtotime($note['issued_at']),
        ':expires_at' => strtotime($note['expires_at'])
    ]);
    
    // Add server to server_status if not present
    $stmt = $db->prepare(
        "INSERT IGNORE INTO server_status (server_id, name, last_seen, status)
         VALUES (:server_id, :name, :last_seen, 'online')"
    );
    
    $stmt->execute([
        ':server_id' => $note['issuer_id'],
        ':name' => $note['issuer'],
        ':last_seen' => time()
    ]);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Bank note imported successfully',
        'bank_note' => [
            'note_id' => $note['note_id'],
            'serial' => $note['serial'],
            'amount' => (float)$note['amount'],
            'issuer' => $note['issuer'],
            'issuer_id' => $note['issuer_id'],
            'status' => 'active',
            'issued_at' => $note['issued_at'],
            'expires_at' => $note['expires_at']
        ],
        'action' => 'deposit'
    ]);
    
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    $suggestedAction = 'retry';
    
    // Customize error messages
    if (strpos($errorMessage, 'signature') !== false) {
        $errorMessage = 'The bank note has an invalid signature and cannot be verified';
        $suggestedAction = 'reject';
    } elseif (strpos($errorMessage, 'expired') !== false) {
        $errorMessage = 'This bank note has expired and cannot be deposited';
        $suggestedAction = 'reject';
    } elseif (strpos($errorMessage, 'already been') !== false) {
        $suggestedAction = 'reject';
    }
    
    echo json_encode([
        'success' => false,
        'message' => $errorMessage,
        'source' => $source,
        'action' => $suggestedAction
    ]);
}
