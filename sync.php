<?php
/**
 * API Sync Endpoint
 * 
 * Handles inter-bank synchronization
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

try {
    // Handle the synchronization request
    $syncAPI = new SyncAPI();
    $syncAPI->handleRequest();
} catch (Exception $e) {
    error_log("Sync error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sync processing error: ' . $e->getMessage()
    ]);
}
