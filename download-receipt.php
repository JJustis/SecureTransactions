<?php
/**
 * API Bank Note Receipt Download Endpoint
 * 
 * Generates a downloadable QR code receipt for a bank note
 */

// Include main implementation
require_once '../../server-implementation.php';

// Process only GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

// Get note ID from query parameter
$noteId = $_GET['id'] ?? '';

if (empty($noteId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing note ID'
    ]);
    exit;
}

try {
    // Get receipt data for the bank note
    $bankNote = new BankNote();
    $receipt = $bankNote->generateReceipt($noteId, $userId);
    
    // Create receipt data to encode in QR code
    $receiptData = [
        'note_id' => $receipt['note_id'],
        'serial' => $receipt['serial'],
        'amount' => $receipt['amount'],
        'issuer' => $receipt['issuer'],
        'date' => $receipt['date'],
        'expiry' => $receipt['expiry'],
        'status' => $receipt['status']
    ];
    
    // Add redemption info if available
    if (isset($receipt['redeemed_at'])) {
        $receiptData['redeemed_at'] = $receipt['redeemed_at'];
        $receiptData['redeemed_by'] = $receipt['redeemed_by'];
    }
    
    // Add transaction ID if available
    if (isset($receipt['transaction_id'])) {
        $receiptData['transaction_id'] = $receipt['transaction_id'];
    }
    
    // Encode the receipt data for the QR code
    $qrCodeData = json_encode($receiptData);
    
    // Generate a QR code using Google Charts API
    $qrCodeUrl = 'https://chart.googleapis.com/chart?';
    $qrCodeUrl .= http_build_query([
        'cht' => 'qr',
        'chs' => '300x300',
        'chl' => $qrCodeData,
        'choe' => 'UTF-8'
    ]);
    
    // Create an HTML page with the QR code and receipt information
    $html = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bank Note Receipt - ' . htmlspecialchars($receipt['serial']) . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            .receipt-container {
                border: 1px solid #ddd;
                padding: 20px;
                margin-bottom: 20px;
                background-color: #f9f9f9;
            }
            .receipt-header {
                text-align: center;
                border-bottom: 2px solid #1a5276;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .receipt-logo {
                font-size: 1.5rem;
                font-weight: bold;
                color: #1a5276;
            }
            .receipt-title {
                font-size: 1.2rem;
                font-weight: bold;
                margin-top: 10px;
            }
            .qr-container {
                text-align: center;
                margin: 20px 0;
            }
            .serial-number {
                font-family: monospace;
                font-size: 1.2rem;
                font-weight: bold;
                text-align: center;
                margin: 10px 0;
                color: #1a5276;
            }
            .receipt-row {
                display: flex;
                padding: 8px 0;
                border-bottom: 1px dashed #ddd;
            }
            .receipt-label {
                flex: 1;
                font-weight: bold;
            }
            .receipt-value {
                flex: 2;
            }
            .receipt-footer {
                text-align: center;
                margin-top: 30px;
                border-top: 1px solid #ddd;
                padding-top: 10px;
                font-size: 0.9rem;
            }
            .receipt-notice {
                margin-bottom: 10px;
                font-style: italic;
            }
            .receipt-signature {
                font-size: 0.8rem;
                color: #666;
            }
            .print-button {
                background-color: #1a5276;
                color: white;
                border: none;
                padding: 10px 15px;
                font-size: 1rem;
                cursor: pointer;
                border-radius: 4px;
                display: block;
                margin: 20px auto;
            }
            @media print {
                .print-button {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="receipt-container">
            <div class="receipt-header">
                <div class="receipt-logo">SecureBank Network</div>
                <div class="receipt-title">BANK NOTE RECEIPT</div>
            </div>
            
            <div class="qr-container">
                <img src="' . $qrCodeUrl . '" alt="Receipt QR Code">
            </div>
            
            <div class="serial-number">
                Serial: ' . htmlspecialchars($receipt['serial']) . '
            </div>
            
            <div class="receipt-details">
                <div class="receipt-row">
                    <div class="receipt-label">Transaction ID:</div>
                    <div class="receipt-value">' . htmlspecialchars($receipt['transaction_id'] ?? 'N/A') . '</div>
                </div>
                
                <div class="receipt-row">
                    <div class="receipt-label">Date:</div>
                    <div class="receipt-value">' . date('Y-m-d H:i:s', strtotime($receipt['date'])) . '</div>
                </div>
                
                <div class="receipt-row">
                    <div class="receipt-label">Note ID:</div>
                    <div class="receipt-value">' . htmlspecialchars($receipt['note_id']) . '</div>
                </div>
                
                <div class="receipt-row">
                    <div class="receipt-label">Amount:</div>
                    <div class="receipt-value">$' . number_format($receipt['amount'], 2) . '</div>
                </div>
                
                <div class="receipt-row">
                    <div class="receipt-label">Issuing Bank:</div>
                    <div class="receipt-value">' . htmlspecialchars($receipt['issuer']) . '</div>
                </div>
                
                <div class="receipt-row">
                    <div class="receipt-label">Expiry Date:</div>
                    <div class="receipt-value">' . date('Y-m-d H:i:s', strtotime($receipt['expiry'])) . '</div>
                </div>';
    
    // Add redemption info if available
    if (isset($receipt['redeemed_at'])) {
        $html .= '
                <div class="receipt-row">
                    <div class="receipt-label">Redeemed At:</div>
                    <div class="receipt-value">' . date('Y-m-d H:i:s', strtotime($receipt['redeemed_at'])) . '</div>
                </div>
                
                <div class="receipt-row">
                    <div class="receipt-label">Redeemed By:</div>
                    <div class="receipt-value">' . htmlspecialchars($receipt['redeemed_by']) . '</div>
                </div>';
    }
    
    $html .= '
            </div>
            
            <div class="receipt-footer">
                <div class="receipt-notice">Keep this receipt for your records.</div>
                <div class="receipt-signature">Electronically Generated - No Signature Required</div>
                <div class="receipt-signature">SecureBank Network - ' . date('Y-m-d H:i:s') . '</div>
            </div>
        </div>
        
        <button class="print-button" onclick="window.print();">Print Receipt</button>
        
        <script>
            // Auto-print in some browsers (optional)
            // window.onload = function() {
            //     window.print();
            // };
        </script>
    </body>
    </html>';
    
    // Output the HTML
    header('Content-Type: text/html');
    echo $html;
    
} catch (Exception $e) {
    // Handle specific errors
    if (strpos($e->getMessage(), 'Unauthorized') !== false) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'You are not authorized to view this receipt'
        ]);
    } elseif (strpos($e->getMessage(), 'not found') !== false) {
        http_response_code(404);
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
