<?php
/**
 * API Bank Note Verify Endpoint
 * 
 * Verifies a bank note without depositing it
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
    // Verify the bank note
    $bankNote = new BankNote();
    $note = $bankNote->verifyBankNote($data['note_identifier']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Bank note verified',
        'bank_note' => $note
    ]);
} catch (Exception $e) {
    // Handle not found specifically
    if (strpos($e->getMessage(), 'not found') !== false) {
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
    }
}
