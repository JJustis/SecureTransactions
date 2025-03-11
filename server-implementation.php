<?php
// Add at the top of your PHP files
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Configuration
class Config {
    // Server identification
    const SERVER_ID = 'bank_server_001';  // Unique identifier for this server
    const SERVER_NAME = 'Bank of A';     // Human-readable name
    
    // Database connection
    const DB_HOST = 'localhost';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_NAME = 'secure_bank_db';
    
    // Security settings
const HASH_ALGORITHM = PASSWORD_BCRYPT; // Compatible with PHP 7.1
const HASH_OPTIONS = [
    'cost' => 12 // BCRYPT parameter
];
    const ENCRYPTION_METHOD = 'aes-256-cbc';
    
    // Known servers in the network
    public static $TRUSTED_SERVERS = [
        'bank_server_002' => [
            'name' => 'Bank of B',
            'endpoint' => 'https://jcmc.serveminecraft.net/liveaccounts/bankofB/api/sync.php',
            'key_id' => 'PSK002',
        ],
        'bank_server_003' => [
            'name' => 'Bank of C',
            'endpoint' => 'https://jcmc.serveminecraft.net/liveaccounts/bankofC/api/sync.php',
            'key_id' => 'PSK003',
        ],
        'bank_server_004' => [
            'name' => 'Regional Credit',
            'endpoint' => 'https://jcmc.serveminecraft.net/liveaccounts/regionalcredit/api/sync.php',
            'key_id' => 'PSK004',
        ]
    ];
    
    // Preshared keys - In production, these would be stored in a secure key management system
    private static $PRESHARED_KEYS = [
        'PSK002' => 'x5GhT8qL2ZvKpN7rXcY3sA9bW4mD6fE1',
        'PSK003' => 'a7Bj9CkD2mF5nG8hP3qR6sT1vWx4zY7',
        'PSK004' => 'k9L4pR7sT2uV5xY8zZ1aB3cD6eF9gH0',
    ];
    
    // Get a specific preshared key by ID
    public static function getPresharedKey($keyId) {
        if (isset(self::$PRESHARED_KEYS[$keyId])) {
            return self::$PRESHARED_KEYS[$keyId];
        }
        throw new Exception("Invalid key ID: $keyId");
    }
}

// Database connection handling
class Database {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME,
                Config::DB_USER,
                Config::DB_PASS
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection error");
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Close the connection
    public function close() {
        $this->conn = null;
    }
}

// Security utilities
class Security {
    // Generate a secure random IV for encryption
    public static function generateIV() {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length(Config::ENCRYPTION_METHOD));
    }
    
    // Encrypt data using a preshared key
    public static function encrypt($data, $keyId) {
        $key = Config::getPresharedKey($keyId);
        $iv = self::generateIV();
        $encrypted = openssl_encrypt(
            json_encode($data),
            Config::ENCRYPTION_METHOD,
            $key,
            0,
            $iv
        );
        
        // Return both the encrypted data and the IV
        return [
            'data' => base64_encode($encrypted),
            'iv' => base64_encode($iv),
            'key_id' => $keyId,
            'timestamp' => time()
        ];
    }
    
    // Decrypt data using a preshared key
    public static function decrypt($encryptedData, $iv, $keyId) {
        $key = Config::getPresharedKey($keyId);
        $decrypted = openssl_decrypt(
            base64_decode($encryptedData),
            Config::ENCRYPTION_METHOD,
            $key,
            0,
            base64_decode($iv)
        );
        
        return json_decode($decrypted, true);
    }
    
    // Hash a username or email for storage
    public static function hashIdentifier($identifier) {
        // Using SHA-256 for deterministic hashing of identifiers
        return hash('sha256', $identifier);
    }
    
public static function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify a password against a stored hash
public static function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
    
    // Generate a message authentication code
    public static function generateMAC($data, $keyId) {
        $key = Config::getPresharedKey($keyId);
        return hash_hmac('sha256', json_encode($data), $key);
    }
    
    // Verify a message authentication code
    public static function verifyMAC($data, $mac, $keyId) {
        $key = Config::getPresharedKey($keyId);
        $calculatedMAC = hash_hmac('sha256', json_encode($data), $key);
        return hash_equals($calculatedMAC, $mac);
    }

// Generate a signature for a bank note
    public static function signData($data) {
        // In a real implementation, this would use asymmetric cryptography
        // For simplicity, we'll use a hash-based signature for this example
        $serverKey = Config::getPresharedKey('PSK002'); // Using any key for signing
        return hash_hmac('sha256', json_encode($data), $serverKey);
    }
    
    // Verify a signature for a bank note
    public static function verifySignature($data, $signature) {
        // Corresponding verification logic
        $serverKey = Config::getPresharedKey('PSK002');
        $calculatedSignature = hash_hmac('sha256', json_encode($data), $serverKey);
        return hash_equals($calculatedSignature, $signature);
    }
}

// User account management
class UserAccount {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Create a new user account
    public function createUser($username, $email, $password, $profileData) {
        // Hash identifiers and password
        $usernameHash = Security::hashIdentifier($username);
        $emailHash = Security::hashIdentifier($email);
        $passwordHash = Security::hashPassword($password);
        
        // Encrypt profile data with the server's own key
        $encryptedProfile = Security::encrypt($profileData, 'PSK002');
        
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (username_hash, email_hash, password_hash, profile_data, iv, balance) 
                 VALUES (:username_hash, :email_hash, :password_hash, :profile_data, :iv, :balance)"
            );
            
            $stmt->execute([
                ':username_hash' => $usernameHash,
                ':email_hash' => $emailHash,
                ':password_hash' => $passwordHash,
                ':profile_data' => $encryptedProfile['data'],
                ':iv' => $encryptedProfile['iv'],
                ':balance' => 1000.00  // Initial balance
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Synchronize with other servers
            $this->syncNewUser($userId, $usernameHash, $emailHash, $passwordHash, $encryptedProfile);
            
            return $userId;
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            throw new Exception("Failed to create user account");
        }
    }
    
    // Authenticate a user
    public function authenticate($username, $password) {
        $usernameHash = Security::hashIdentifier($username);
        
        try {
            $stmt = $this->db->prepare(
                "SELECT id, password_hash FROM users WHERE username_hash = :username_hash"
            );
            $stmt->execute([':username_hash' => $usernameHash]);
            
            $user = $stmt->fetch();
            
            if ($user && Security::verifyPassword($password, $user['password_hash'])) {
                // Generate session token
                $session = $this->createSession($user['id']);
                
                // Sync session with other servers
                $this->syncSession($session);
                
                return $session;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Authentication error: " . $e->getMessage());
            throw new Exception("Authentication failed");
        }
    }
    
    // Get account information
    public function getAccountInfo($userId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT u.id, u.balance, u.profile_data, u.iv 
                 FROM users u
                 WHERE u.id = :user_id"
            );
            
            $stmt->execute([':user_id' => $userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            // Decrypt profile data
            $profileData = Security::decrypt(
                $user['profile_data'],
                $user['iv'],
                'PSK002'
            );
            
            return [
                'userId' => $user['id'],
                'balance' => (float)$user['balance'],
                'profile' => $profileData,
                'username' => $profileData['fullName'] ?? 'User'
            ];
        } catch (PDOException $e) {
            error_log("Error getting account info: " . $e->getMessage());
            throw new Exception("Failed to retrieve account information");
        }
    }
    
    // Get transaction history
    public function getTransactions($userId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT t.transaction_id, t.transaction_type, t.amount, t.reference, 
                        t.timestamp, t.balance_after
                 FROM transactions t
                 WHERE t.user_id = :user_id
                 ORDER BY t.timestamp DESC
                 LIMIT 50"
            );
            
            $stmt->execute([':user_id' => $userId]);
            
            $transactions = [];
            while ($row = $stmt->fetch()) {
                $transactions[] = [
                    'id' => $row['transaction_id'],
                    'type' => $row['transaction_type'],
                    'amount' => (float)$row['amount'],
                    'description' => $row['reference'],
                    'timestamp' => date('c', $row['timestamp']),
                    'balance_after' => (float)$row['balance_after']
                ];
            }
            
            return $transactions;
        } catch (PDOException $e) {
            error_log("Error getting transactions: " . $e->getMessage());
            throw new Exception("Failed to retrieve transaction history");
        }
    }
    
    // Create a new session for authenticated user
    private function createSession($userId) {
        $sessionId = bin2hex(random_bytes(32));
        $expiry = time() + 3600; // 1 hour expiry
        
        $stmt = $this->db->prepare(
            "INSERT INTO sessions (session_id, user_id, expiry) 
             VALUES (:session_id, :user_id, :expiry)"
        );
        
        $stmt->execute([
            ':session_id' => $sessionId,
            ':user_id' => $userId,
            ':expiry' => $expiry
        ]);
        
        return [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'expiry' => $expiry
        ];
    }
    
    // Update account balance
    public function updateBalance($userId, $amount, $transactionId, $transactionType = 'adjustment', $reference = '') {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Check if transaction already processed (idempotence)
            $stmt = $this->db->prepare(
                "SELECT id FROM transactions WHERE transaction_id = :transaction_id"
            );
            $stmt->execute([':transaction_id' => $transactionId]);
            
            if ($stmt->rowCount() > 0) {
                // Transaction already processed
                $this->db->rollBack();
                return false;
            }
            
            // Get current balance
            $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->db->rollBack();
                throw new Exception("User not found");
            }
            
            $currentBalance = (float)$user['balance'];
            $newBalance = $currentBalance + $amount;
            
            // Prevent negative balance
            if ($newBalance < 0) {
                $this->db->rollBack();
                throw new Exception("Insufficient funds");
            }
            
            // Update balance
            $stmt = $this->db->prepare(
                "UPDATE users SET balance = :balance WHERE id = :user_id"
            );
            $stmt->execute([
                ':balance' => $newBalance,
                ':user_id' => $userId
            ]);
            
            // Record transaction
            $stmt = $this->db->prepare(
                "INSERT INTO transactions (transaction_id, user_id, transaction_type, amount, 
                                         balance_after, reference, timestamp) 
                 VALUES (:transaction_id, :user_id, :transaction_type, :amount, 
                        :balance_after, :reference, :timestamp)"
            );
            $stmt->execute([
                ':transaction_id' => $transactionId,
                ':user_id' => $userId,
                ':transaction_type' => $transactionType,
                ':amount' => $amount,
                ':balance_after' => $newBalance,
                ':reference' => $reference,
                ':timestamp' => time()
            ]);
            
            // Commit transaction
            $this->db->commit();
            
            // Sync with other servers
            $this->syncBalance($userId, $newBalance, $transactionId, $amount, $transactionType, $reference);
            
            return [
                'success' => true,
                'new_balance' => $newBalance,
                'transaction_id' => $transactionId
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Balance update error: " . $e->getMessage());
            throw new Exception("Failed to update balance: " . $e->getMessage());
        }
    }
    
    // Synchronize a new user to all other servers
    private function syncNewUser($userId, $usernameHash, $emailHash, $passwordHash, $profileData) {
        $syncData = [
            'action' => 'new_user',
            'user_id' => $userId,
            'username_hash' => $usernameHash,
            'email_hash' => $emailHash,
            'password_hash' => $passwordHash,
            'profile_data' => $profileData,
            'balance' => 1000.00 // Initial balance
        ];
        
        $this->syncToAllServers($syncData);
    }
    
    // Synchronize a session to all other servers
    private function syncSession($session) {
        $syncData = [
            'action' => 'new_session',
            'session' => $session
        ];
        
        $this->syncToAllServers($syncData);
    }
    
    // Synchronize a balance update to all other servers
    private function syncBalance($userId, $newBalance, $transactionId, $amount, $transactionType, $reference) {
        $syncData = [
            'action' => 'balance_update',
            'user_id' => $userId,
            'balance' => $newBalance,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'transaction_type' => $transactionType,
            'reference' => $reference,
            'timestamp' => time()
        ];
        
        $this->syncToAllServers($syncData);
    }
    
    // Synchronize data to all other servers in the network
    public function syncToAllServers($data) {
        foreach (Config::$TRUSTED_SERVERS as $serverId => $serverInfo) {
            if ($serverId === Config::SERVER_ID) {
                continue; // Skip self
            }
            
            $this->sendToServer($data, $serverInfo['endpoint'], $serverInfo['key_id']);
        }
    }
    
    // Send data to a specific server
    private function sendToServer($data, $endpoint, $keyId) {
        // Encrypt the data using the server's preshared key
        $encryptedPackage = Security::encrypt($data, $keyId);
        
        // Generate MAC for additional verification
        $mac = Security::generateMAC($encryptedPackage, $keyId);
        
        // Prepare the final payload
        $payload = [
            'sender_id' => Config::SERVER_ID,
            'encrypted' => $encryptedPackage,
            'mac' => $mac
        ];
        
        // Send using cURL
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            error_log("Sync failed: " . curl_error($ch));
            // Queue for retry later
            $this->queueFailedSync($data, $endpoint, $keyId);
        } else {
            $responseData = json_decode($response, true);
            if (!isset($responseData['success']) || $responseData['success'] !== true) {
                error_log("Sync error: " . json_encode($responseData));
                $this->queueFailedSync($data, $endpoint, $keyId);
            }
        }
        
        curl_close($ch);
    }
    
    // Queue failed synchronization attempts for later retry
    private function queueFailedSync($data, $endpoint, $keyId) {
        $stmt = $this->db->prepare(
            "INSERT INTO sync_queue (data, endpoint, key_id, attempts, next_attempt) 
             VALUES (:data, :endpoint, :key_id, :attempts, :next_attempt)"
        );
        
        $stmt->execute([
            ':data' => json_encode($data),
            ':endpoint' => $endpoint,
            ':key_id' => $keyId,
            ':attempts' => 0,
            ':next_attempt' => time() + 300 // Try again in 5 minutes
        ]);
    }
}

/**
 * Bank Note Management
 */
class BankNote {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Create a new bank note
     */
    public function createBankNote($noteId, $serialNumber, $amount, $expiryDays, $userId) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Check if user exists and has sufficient funds
            $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->db->rollBack();
                throw new Exception("User not found");
            }
            
            if ($user['balance'] < $amount) {
                $this->db->rollBack();
                throw new Exception("Insufficient funds");
            }
            
            // Calculate timestamps
            $issuedAt = time();
            $expiresAt = strtotime("+{$expiryDays} days", $issuedAt);
            
            // Create signature data
            $signatureData = [
                'note_id' => $noteId,
                'serial' => $serialNumber,
                'amount' => $amount,
                'issuer' => Config::SERVER_ID,
                'issued_at' => $issuedAt,
                'expires_at' => $expiresAt,
                'user_id' => $userId
            ];
            
            // Generate signature
            $signature = Security::signData($signatureData);
            
            // Insert bank note record
            $stmt = $this->db->prepare(
                "INSERT INTO bank_notes (
                    note_id, serial_number, amount, issuer_id, 
                    user_id, status, signature, issued_at, expires_at
                ) VALUES (
                    :note_id, :serial_number, :amount, :issuer_id,
                    :user_id, 'active', :signature, :issued_at, :expires_at
                )"
            );
            
            $stmt->execute([
                ':note_id' => $noteId,
                ':serial_number' => $serialNumber,
                ':amount' => $amount,
                ':issuer_id' => Config::SERVER_ID,
                ':user_id' => $userId,
                ':signature' => $signature,
                ':issued_at' => $issuedAt,
                ':expires_at' => $expiresAt
            ]);
            
            // Generate transaction ID
            $transactionId = 'txn_' . bin2hex(random_bytes(16));
            
            // Update user balance
            $userAccount = new UserAccount();
            $balanceResult = $userAccount->updateBalance(
                $userId,
                -$amount, // Negative amount for withdrawal
                $transactionId,
                'bank_note_withdrawal',
                "Bank note withdrawal: {$serialNumber}"
            );
            
            if (!$balanceResult['success']) {
                $this->db->rollBack();
                throw new Exception("Failed to update balance");
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Sync bank note to other servers
            $this->syncBankNote($noteId);
            
            // Return bank note data
            return [
                'note_id' => $noteId,
                'serial' => $serialNumber,
                'amount' => (float)$amount,
                'issuer' => Config::SERVER_NAME,
                'issuer_id' => Config::SERVER_ID,
                'issued_at' => date('c', $issuedAt),
                'expires_at' => date('c', $expiresAt),
                'status' => 'active',
                'signature' => $signature,
                'transaction_id' => $transactionId
            ];
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Get a bank note by ID or serial number
     */
 public function getBankNote($identifier) {
    try {
        // First check if the bank_notes table exists
        $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('bank_notes', $tables)) {
            error_log("bank_notes table does not exist");
            throw new Exception("Database setup incomplete");
        }
        
        // Simplify the query to avoid join issues
        $stmt = $this->db->prepare(
            "SELECT * FROM bank_notes 
             WHERE note_id = :identifier 
                OR serial_number = :identifier
             LIMIT 1"
        );
        
        $stmt->execute([':identifier' => $identifier]);
        $note = $stmt->fetch();
        
        if (!$note) {
            error_log("Bank note not found: " . $identifier);
            // Try verifying with other servers
            return $this->verifyWithOtherServers($identifier);
        }
        
        // Return note data without depending on joined tables
        return [
            'note_id' => $note['note_id'],
            'serial' => $note['serial_number'],
            'amount' => (float)$note['amount'],
            'issuer' => $note['issuer_id'], // Use issuer_id directly
            'issuer_id' => $note['issuer_id'],
            'status' => $note['status'],
            'signature' => $note['signature'],
            'issued_at' => date('c', $note['issued_at']),
            'expires_at' => date('c', $note['expires_at']),
            'redeemed_at' => isset($note['redeemed_at']) && $note['redeemed_at'] ? date('c', $note['redeemed_at']) : null,
            'redeemed_by' => $note['redeemed_by'] ?? null
        ];
        
    } catch (PDOException $e) {
        error_log("Database error getting bank note: " . $e->getMessage());
        throw new Exception("Failed to retrieve bank note: DB error - " . $e->getMessage());
    }
}
    /**
     * Get bank notes created by a user
     */
    public function getCreatedNotes($userId, $status = null) {
        try {
            $query = "
                SELECT bn.*, s.name as server_name, 
                       rs.name as redeeming_server_name
                FROM bank_notes bn
                LEFT JOIN server_status s ON bn.issuer_id = s.server_id
                LEFT JOIN server_status rs ON bn.redeemed_by = rs.server_id
                WHERE bn.user_id = :user_id
            ";
            
            // Add status filter if provided
            if ($status && $status !== 'all') {
                $query .= " AND bn.status = :status";
            }
            
            $query .= " ORDER BY bn.issued_at DESC";
            
            $stmt = $this->db->prepare($query);
            
            $params = [':user_id' => $userId];
            if ($status && $status !== 'all') {
                $params[':status'] = $status;
            }
            
            $stmt->execute($params);
            
            $notes = [];
            while ($note = $stmt->fetch()) {
                $notes[] = [
                    'note_id' => $note['note_id'],
                    'serial' => $note['serial_number'],
                    'amount' => (float)$note['amount'],
                    'issuer' => $note['server_name'] ?? $note['issuer_id'],
                    'issuer_id' => $note['issuer_id'],
                    'status' => $note['status'],
                    'signature' => $note['signature'],
                    'issued_at' => date('c', $note['issued_at']),
                    'expires_at' => date('c', $note['expires_at']),
                    'redeemed_at' => $note['redeemed_at'] ? date('c', $note['redeemed_at']) : null,
                    'redeemed_by' => $note['redeeming_server_name'] ?? $note['redeemed_by']
                ];
            }
            
            return $notes;
            
        } catch (PDOException $e) {
            error_log("Error getting created notes: " . $e->getMessage());
            throw new Exception("Failed to retrieve created bank notes");
        }
    }
    
    /**
     * Get bank notes received (redeemed) by a user
     */
    public function getReceivedNotes($userId, $status = null) {
        try {
            $query = "
                SELECT bn.*, s.name as server_name, 
                       rs.name as redeeming_server_name
                FROM bank_notes bn
                LEFT JOIN server_status s ON bn.issuer_id = s.server_id
                LEFT JOIN server_status rs ON bn.redeemed_by = rs.server_id
                WHERE bn.redemption_user_id = :user_id
            ";
            
            // Add status filter if provided
            if ($status && $status !== 'all') {
                $query .= " AND bn.status = :status";
            }
            
            $query .= " ORDER BY bn.redeemed_at DESC";
            
            $stmt = $this->db->prepare($query);
            
            $params = [':user_id' => $userId];
            if ($status && $status !== 'all') {
                $params[':status'] = $status;
            }
            
            $stmt->execute($params);
            
            $notes = [];
            while ($note = $stmt->fetch()) {
                $notes[] = [
                    'note_id' => $note['note_id'],
                    'serial' => $note['serial_number'],
                    'amount' => (float)$note['amount'],
                    'issuer' => $note['server_name'] ?? $note['issuer_id'],
                    'issuer_id' => $note['issuer_id'],
                    'status' => $note['status'],
                    'signature' => $note['signature'],
                    'issued_at' => date('c', $note['issued_at']),
                    'expires_at' => date('c', $note['expires_at']),
                    'redeemed_at' => $note['redeemed_at'] ? date('c', $note['redeemed_at']) : null,
                    'redeemed_by' => $note['redeeming_server_name'] ?? $note['redeemed_by']
                ];
            }
            
            return $notes;
            
        } catch (PDOException $e) {
            error_log("Error getting received notes: " . $e->getMessage());
            throw new Exception("Failed to retrieve received bank notes");
        }
    }
/**
     * Verify a bank note with other servers
     */
    private function verifyWithOtherServers($identifier) {
        // Prepare verification request
        $verificationData = [
            'action' => 'verify_bank_note',
            'note_identifier' => $identifier
        ];
        
        // Try each server until we find the note
        foreach (Config::$TRUSTED_SERVERS as $serverId => $serverInfo) {
            if ($serverId === Config::SERVER_ID) {
                continue; // Skip self
            }
            
            try {
                // Send verification request
                $encryptedPackage = Security::encrypt($verificationData, $serverInfo['key_id']);
                $mac = Security::generateMAC($encryptedPackage, $serverInfo['key_id']);
                
                $payload = [
                    'sender_id' => Config::SERVER_ID,
                    'encrypted' => $encryptedPackage,
                    'mac' => $mac
                ];
                
                $ch = curl_init($serverInfo['endpoint']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                curl_close($ch);
                
                if ($response) {
                    $responseData = json_decode($response, true);
                    
                    if (isset($responseData['success']) && $responseData['success'] && 
                        isset($responseData['bank_note'])) {
                        
                        // Store this note in our database
                        $this->storeForeignBankNote($responseData['bank_note']);
                        
                        return $responseData['bank_note'];
                    }
                }
            } catch (Exception $e) {
                // Log error and continue to next server
                error_log("Error verifying with server {$serverId}: " . $e->getMessage());
                continue;
            }
        }
        
        // If we get here, no server could verify the note
        throw new Exception("Bank note not found or invalid");
    }
    
    /**
     * Store a bank note from another server
     */
    private function storeForeignBankNote($note) {
        try {
            // Check if we already have this note
            $stmt = $this->db->prepare(
                "SELECT id FROM bank_notes WHERE note_id = :note_id LIMIT 1"
            );
            $stmt->execute([':note_id' => $note['note_id']]);
            
            if ($stmt->rowCount() > 0) {
                // We already have this note, update if needed
                $stmt = $this->db->prepare(
                    "UPDATE bank_notes SET
                        status = :status,
                        redeemed_at = :redeemed_at,
                        redeemed_by = :redeemed_by,
                        redemption_user_id = :redemption_user_id
                     WHERE note_id = :note_id"
                );
                
                $stmt->execute([
                    ':status' => $note['status'],
                    ':redeemed_at' => isset($note['redeemed_at']) ? strtotime($note['redeemed_at']) : null,
                    ':redeemed_by' => $note['redeemed_by'] ?? null,
                    ':redemption_user_id' => $note['redemption_user_id'] ?? null,
                    ':note_id' => $note['note_id']
                ]);
            } else {
                // Insert the new note
                $stmt = $this->db->prepare(
                    "INSERT INTO bank_notes (
                        note_id, serial_number, amount, issuer_id,
                        user_id, status, signature, issued_at, expires_at,
                        redeemed_at, redeemed_by, redemption_user_id
                    ) VALUES (
                        :note_id, :serial_number, :amount, :issuer_id,
                        :user_id, :status, :signature, :issued_at, :expires_at,
                        :redeemed_at, :redeemed_by, :redemption_user_id
                    )"
                );
                
                $stmt->execute([
                    ':note_id' => $note['note_id'],
                    ':serial_number' => $note['serial'],
                    ':amount' => $note['amount'],
                    ':issuer_id' => $note['issuer_id'],
                    ':user_id' => $note['user_id'] ?? 0,
                    ':status' => $note['status'],
                    ':signature' => $note['signature'],
                    ':issued_at' => strtotime($note['issued_at']),
                    ':expires_at' => strtotime($note['expires_at']),
                    ':redeemed_at' => isset($note['redeemed_at']) ? strtotime($note['redeemed_at']) : null,
                    ':redeemed_by' => $note['redeemed_by'] ?? null,
                    ':redemption_user_id' => $note['redemption_user_id'] ?? null
                ]);
            }
            
            // Add server to server_status if not present
            if ($note['issuer_id']) {
                $stmt = $this->db->prepare(
                    "INSERT IGNORE INTO server_status (server_id, name, last_seen)
                     VALUES (:server_id, :name, :last_seen)"
                );
                
                $stmt->execute([
                    ':server_id' => $note['issuer_id'],
                    ':name' => $note['issuer'],
                    ':last_seen' => time()
                ]);
            }
            
            if ($note['redeemed_by']) {
$stmt = $this->db->prepare(
                    "INSERT IGNORE INTO server_status (server_id, name, last_seen)
                     VALUES (:server_id, :name, :last_seen)"
                );
                
                $stmt->execute([
                    ':server_id' => $note['redeemed_by'],
                    ':name' => $note['redeemed_by_name'] ?? $note['redeemed_by'],
                    ':last_seen' => time()
                ]);
            }
            
        } catch (PDOException $e) {
            error_log("Error storing foreign bank note: " . $e->getMessage());
            // Continue anyway, this is not critical
        }
    }
    
    /**
     * Verify a bank note without depositing
     */
    public function verifyBankNote($identifier) {
        // First try to get from our database
        try {
            return $this->getBankNote($identifier);
        } catch (Exception $e) {
            // If not found, throw the error
            throw new Exception("Bank note verification failed: " . $e->getMessage());
        }
    }
    
    /**
     * Deposit (redeem) a bank note
     */
    public function depositBankNote($identifier, $userId) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Get the bank note
            $note = $this->verifyBankNote($identifier);
            
            // Check if note is active
            if ($note['status'] !== 'active') {
                $this->db->rollBack();
                throw new Exception("Bank note is " . strtolower($note['status']) . " and cannot be deposited");
            }
            
            // Check if note has expired
            if (strtotime($note['expires_at']) < time()) {
                $this->db->rollBack();
                throw new Exception("Bank note has expired");
            }
            
            // Mark the note as redeemed
            $stmt = $this->db->prepare(
                "UPDATE bank_notes SET
                    status = 'redeemed',
                    redeemed_at = :redeemed_at,
                    redeemed_by = :redeemed_by,
                    redemption_user_id = :redemption_user_id
                 WHERE note_id = :note_id
                    OR serial_number = :identifier"
            );
            
            $redemptionTime = time();
            
            $stmt->execute([
                ':redeemed_at' => $redemptionTime,
                ':redeemed_by' => Config::SERVER_ID,
                ':redemption_user_id' => $userId,
                ':note_id' => $note['note_id'],
                ':identifier' => $identifier
            ]);
            
            // Add funds to user's account
            $transactionId = 'txn_' . bin2hex(random_bytes(16));
            $reference = "Bank note deposit: {$note['serial']} from {$note['issuer']}";
            
            $userAccount = new UserAccount();
            $balanceResult = $userAccount->updateBalance(
                $userId,
                $note['amount'],
                $transactionId,
                'bank_note_deposit',
                $reference
            );
            
            if (!$balanceResult['success']) {
                $this->db->rollBack();
                throw new Exception("Failed to update balance");
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Sync redemption to other servers
            $this->syncRedemption($note['note_id'], $userId, $redemptionTime);
            
            // Update the note data with redemption info
            $note['status'] = 'redeemed';
            $note['redeemed_at'] = date('c', $redemptionTime);
            $note['redeemed_by'] = Config::SERVER_NAME;
            $note['transaction_id'] = $transactionId;
            
            return $note;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Generate a receipt for a bank note
     */
    public function generateReceipt($noteId, $userId) {
        try {
            // Get the bank note
            $note = $this->getBankNote($noteId);
            
            // Check if user is authorized to view this receipt
            $stmt = $this->db->prepare(
                "SELECT 1 FROM bank_notes 
                 WHERE (note_id = :note_id OR serial_number = :note_id)
                 AND (user_id = :user_id OR redemption_user_id = :user_id)
                 LIMIT 1"
            );
            
            $stmt->execute([
                ':note_id' => $noteId,
                ':user_id' => $userId
            ]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Unauthorized access to receipt");
            }
            
            // Get the transaction ID from transactions table
            $stmt = $this->db->prepare(
                "SELECT transaction_id FROM transactions
                 WHERE user_id = :user_id
                 AND reference LIKE :reference
                 ORDER BY timestamp DESC
                 LIMIT 1"
            );
            
            $stmt->execute([
                ':user_id' => $userId,
                ':reference' => "%{$note['serial']}%"
            ]);
            
            $transaction = $stmt->fetch();
            $transactionId = $transaction ? $transaction['transaction_id'] : null;
            
            // Create receipt data
            $receipt = [
                'transaction_id' => $transactionId,
                'note_id' => $note['note_id'],
                'serial' => $note['serial'],
                'amount' => $note['amount'],
                'issuer' => $note['issuer'],
                'date' => $note['issued_at'],
                'expiry' => $note['expires_at'],
                'status' => $note['status']
            ];
            
            // Add redemption info if available
            if ($note['status'] === 'redeemed' && $note['redeemed_at']) {
                $receipt['redeemed_at'] = $note['redeemed_at'];
                $receipt['redeemed_by'] = $note['redeemed_by'];
            }
            
            return $receipt;
            
        } catch (Exception $e) {
            throw new Exception("Failed to generate receipt: " . $e->getMessage());
        }
    }
    
    /**
     * Synchronize a bank note to other servers
     */
    private function syncBankNote($noteId) {
        try {
            // Get the bank note
            $stmt = $this->db->prepare("SELECT * FROM bank_notes WHERE note_id = :note_id");
            $stmt->execute([':note_id' => $noteId]);
            $note = $stmt->fetch();
            
            if (!$note) {
                throw new Exception("Bank note not found");
            }
            
            // Prepare sync data
            $syncData = [
                'action' => 'bank_note_created',
                'note_id' => $note['note_id'],
                'serial' => $note['serial_number'],
                'amount' => (float)$note['amount'],
                'issuer' => Config::SERVER_NAME,
                'issuer_id' => $note['issuer_id'],
                'user_id' => $note['user_id'],
                'status' => $note['status'],
                'signature' => $note['signature'],
                'issued_at' => date('c', $note['issued_at']),
                'expires_at' => date('c', $note['expires_at'])
            ];
            
            // Sync to all servers
            $userAccount = new UserAccount();
            $userAccount->syncToAllServers($syncData);
            
        } catch (Exception $e) {
            error_log("Error syncing bank note: " . $e->getMessage());
            // Continue execution, this is not critical
        }
    }
    
    /**
     * Synchronize a bank note redemption to other servers
     */
    private function syncRedemption($noteId, $userId, $redemptionTime) {
        try {
            // Prepare sync data
            $syncData = [
                'action' => 'bank_note_redeemed',
                'note_id' => $noteId,
                'redemption_user_id' => $userId,
                'redeemed_by' => Config::SERVER_ID,
                'redeemed_by_name' => Config::SERVER_NAME,
                'redeemed_at' => date('c', $redemptionTime)
            ];
            
            // Sync to all servers
            $userAccount = new UserAccount();
            $userAccount->syncToAllServers($syncData);
            
        } catch (Exception $e) {
            error_log("Error syncing redemption: " . $e->getMessage());
            // Continue execution, this is not critical
        }
    }
    
    /**
     * Import a bank note from JSON
     */
    public function importBankNote($noteJson) {
        try {
            // Decode the note JSON
            $noteData = json_decode($noteJson, true);
            
            if (!$noteData || !isset($noteData['securebank_note'])) {
                throw new Exception("Invalid bank note format");
            }
            
            $note = $noteData['securebank_note'];
            
            // Validate required fields
            $requiredFields = ['note_id', 'serial', 'amount', 'issuer', 'issuer_id', 'issued_at', 'expires_at', 'signature'];
            foreach ($requiredFields as $field) {
                if (!isset($note[$field])) {
                    throw new Exception("Missing required field: {$field}");
                }
            }
            
            // Check if the note already exists
            try {
                $existingNote = $this->getBankNote($note['note_id']);
                return $existingNote; // Return the existing note
            } catch (Exception $e) {
                // Note doesn't exist, continue with import
            }
            
            // Verify signature
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
            
            // Check if note has expired
            if (strtotime($note['expires_at']) < time()) {
                throw new Exception("Bank note has expired");
            }
            
            // Store the note in our database
            $stmt = $this->db->prepare(
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
            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO server_status (server_id, name, last_seen, status)
                 VALUES (:server_id, :name, :last_seen, 'online')"
            );
            
            $stmt->execute([
                ':server_id' => $note['issuer_id'],
                ':name' => $note['issuer'],
                ':last_seen' => time()
            ]);
            
            // Return imported note data
            return [
                'note_id' => $note['note_id'],
                'serial' => $note['serial'],
                'amount' => (float)$note['amount'],
                'issuer' => $note['issuer'],
                'issuer_id' => $note['issuer_id'],
                'status' => 'active',
                'signature' => $note['signature'],
                'issued_at' => $note['issued_at'],
                'expires_at' => $note['expires_at']
            ];
            
        } catch (Exception $e) {
            throw new Exception("Bank note import failed: " . $e->getMessage());
        }
    }
}

// API endpoints for inter-server communication
class SyncAPI {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Handle incoming sync requests from other servers
    public function handleRequest() {
        // Get the raw POST data
        $rawData = file_get_contents('php://input');
        $payload = json_decode($rawData, true);
        
        // Basic validation
        if (!isset($payload['sender_id']) || 
            !isset($payload['encrypted']) || 
            !isset($payload['mac'])) {
            $this->sendResponse(false, 'Invalid request format');
            return;
        }
        
        $senderId = $payload['sender_id'];
        $encrypted = $payload['encrypted'];
        $mac = $payload['mac'];
        
        // Verify sender is a trusted server
        if (!isset(Config::$TRUSTED_SERVERS[$senderId])) {
            $this->sendResponse(false, 'Untrusted sender');
            return;
        }
        
        $serverInfo = Config::$TRUSTED_SERVERS[$senderId];
        $keyId = $serverInfo['key_id'];
        
        // Verify MAC
        if (!Security::verifyMAC($encrypted, $mac, $keyId)) {
            $this->sendResponse(false, 'MAC verification failed');
            return;
        }
        
        // Decrypt the data
        try {
            $data = Security::decrypt(
                $encrypted['data'],
                $encrypted['iv'],
                $keyId
            );
            
            // Verify timestamp to prevent replay attacks (5 minute window)
            if (time() - $encrypted['timestamp'] > 300) {
                $this->sendResponse(false, 'Timestamp expired');
                return;
            }
            
            // Process the sync action
            $result = $this->processSync($data);
            
            // Return response
            if (is_array($result) && !empty($result)) {
                $this->sendResponse(true, 'Sync successful', $result);
            } else {
                $this->sendResponse(true, 'Sync successful');
            }
        } catch (Exception $e) {
            error_log("Sync processing error: " . $e->getMessage());
            $this->sendResponse(false, 'Processing error: ' . $e->getMessage());
        }
    }
    
    // Process different types of sync actions
    private function processSync($data) {
        if (!isset($data['action'])) {
            throw new Exception('No action specified');
        }
        
        switch ($data['action']) {
            case 'new_user':
                $this->processNewUser($data);
                break;
                
            case 'new_session':
                $this->processNewSession($data);
                break;
                
            case 'balance_update':
                $this->processBalanceUpdate($data);
                break;
                
            case 'bank_note_created':
                $this->processBankNoteCreated($data);
                break;
                
            case 'bank_note_redeemed':
                $this->processBankNoteRedeemed($data);
                break;
                
            case 'verify_bank_note':
                return $this->processVerifyBankNote($data);
                
            default:
                throw new Exception('Unknown action: ' . $data['action']);
        }
        
        return null;
    }
// Process a new user sync
    private function processNewUser($data) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (id, username_hash, email_hash, password_hash, profile_data, iv, balance) 
                 VALUES (:id, :username_hash, :email_hash, :password_hash, :profile_data, :iv, :balance)
                 ON DUPLICATE KEY UPDATE 
                 username_hash = :username_hash,
                 email_hash = :email_hash,
                 password_hash = :password_hash,
                 profile_data = :profile_data,
                 iv = :iv,
                 balance = :balance"
            );
            
            $stmt->execute([
                ':id' => $data['user_id'],
                ':username_hash' => $data['username_hash'],
                ':email_hash' => $data['email_hash'],
                ':password_hash' => $data['password_hash'],
                ':profile_data' => $data['profile_data']['data'],
                ':iv' => $data['profile_data']['iv'],
                ':balance' => $data['balance']
            ]);
        } catch (PDOException $e) {
            error_log("Error processing new user sync: " . $e->getMessage());
            throw new Exception("Failed to sync new user");
        }
    }
    
    // Process a new session sync
    private function processNewSession($data) {
        try {
            $session = $data['session'];
            
            $stmt = $this->db->prepare(
                "INSERT INTO sessions (session_id, user_id, expiry) 
                 VALUES (:session_id, :user_id, :expiry)
                 ON DUPLICATE KEY UPDATE expiry = :expiry"
            );
            
            $stmt->execute([
                ':session_id' => $session['session_id'],
                ':user_id' => $session['user_id'],
                ':expiry' => $session['expiry']
            ]);
        } catch (PDOException $e) {
            error_log("Error processing session sync: " . $e->getMessage());
            throw new Exception("Failed to sync session");
        }
    }
    
    // Process a balance update sync
    private function processBalanceUpdate($data) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Check if transaction already processed (idempotence)
            $stmt = $this->db->prepare(
                "SELECT id FROM transactions WHERE transaction_id = :transaction_id"
            );
            $stmt->execute([':transaction_id' => $data['transaction_id']]);
            
            if ($stmt->rowCount() > 0) {
                // Transaction already processed
                $this->db->rollBack();
                return;
            }
            
            // Update balance
            $stmt = $this->db->prepare(
                "UPDATE users SET balance = :balance WHERE id = :user_id"
            );
            $stmt->execute([
                ':balance' => $data['balance'],
                ':user_id' => $data['user_id']
            ]);
            
            // Record transaction
            $stmt = $this->db->prepare(
                "INSERT INTO transactions (
                    transaction_id, user_id, transaction_type, 
                    amount, balance_after, reference, timestamp
                ) VALUES (
                    :transaction_id, :user_id, :transaction_type,
                    :amount, :balance_after, :reference, :timestamp
                )"
            );
            $stmt->execute([
                ':transaction_id' => $data['transaction_id'],
                ':user_id' => $data['user_id'],
                ':transaction_type' => $data['transaction_type'] ?? 'adjustment',
                ':amount' => $data['amount'] ?? 0,
                ':balance_after' => $data['balance'],
                ':reference' => $data['reference'] ?? '',
                ':timestamp' => $data['timestamp']
            ]);
            
            // Commit transaction
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error processing balance update sync: " . $e->getMessage());
            throw new Exception("Failed to sync balance update");
        }
    }
    
    // Process a bank note creation sync
    private function processBankNoteCreated($data) {
        try {
            // Check if we already have this note
            $stmt = $this->db->prepare(
                "SELECT id FROM bank_notes WHERE note_id = :note_id"
            );
            $stmt->execute([':note_id' => $data['note_id']]);
            
            if ($stmt->rowCount() > 0) {
                // Already have this note, nothing to do
                return;
            }
            
            // Insert the bank note
            $stmt = $this->db->prepare(
                "INSERT INTO bank_notes (
                    note_id, serial_number, amount, issuer_id, 
                    user_id, status, signature, issued_at, expires_at
                ) VALUES (
                    :note_id, :serial, :amount, :issuer_id,
                    :user_id, :status, :signature, :issued_at, :expires_at
                )"
            );
            
            $stmt->execute([
                ':note_id' => $data['note_id'],
                ':serial' => $data['serial'],
                ':amount' => $data['amount'],
                ':issuer_id' => $data['issuer_id'],
                ':user_id' => $data['user_id'],
                ':status' => $data['status'],
                ':signature' => $data['signature'],
                ':issued_at' => strtotime($data['issued_at']),
                ':expires_at' => strtotime($data['expires_at'])
            ]);
            
            // Add server to server_status if not present
            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO server_status (server_id, name, last_seen, endpoint, status)
                 VALUES (:server_id, :name, :last_seen, :endpoint, 'online')"
            );
            
            $stmt->execute([
                ':server_id' => $data['issuer_id'],
                ':name' => $data['issuer'],
                ':last_seen' => time(),
                ':endpoint' => Config::$TRUSTED_SERVERS[$data['issuer_id']]['endpoint'] ?? ''
            ]);
            
        } catch (PDOException $e) {
            error_log("Error processing bank note creation: " . $e->getMessage());
            throw new Exception("Failed to sync bank note creation");
        }
    }
    
    // Process a bank note redemption sync
    private function processBankNoteRedeemed($data) {
        try {
            // Update the bank note status
            $stmt = $this->db->prepare(
                "UPDATE bank_notes SET
                    status = 'redeemed',
                    redeemed_at = :redeemed_at,
                    redeemed_by = :redeemed_by,
                    redemption_user_id = :redemption_user_id
                 WHERE note_id = :note_id"
            );
            
            $stmt->execute([
                ':redeemed_at' => strtotime($data['redeemed_at']),
                ':redeemed_by' => $data['redeemed_by'],
                ':redemption_user_id' => $data['redemption_user_id'],
                ':note_id' => $data['note_id']
            ]);
            
            // Add redeeming server to server_status if not present
            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO server_status (server_id, name, last_seen, status)
                 VALUES (:server_id, :name, :last_seen, 'online')"
            );
            
            $stmt->execute([
                ':server_id' => $data['redeemed_by'],
                ':name' => $data['redeemed_by_name'] ?? $data['redeemed_by'],
                ':last_seen' => time()
            ]);
            
        } catch (PDOException $e) {
            error_log("Error processing bank note redemption: " . $e->getMessage());
            throw new Exception("Failed to sync bank note redemption");
        }
    }
    
    // Process a bank note verification request
    private function processVerifyBankNote($data) {
        try {
            $identifier = $data['note_identifier'];
            
            // Get the bank note
            $bankNote = new BankNote();
            $note = $bankNote->getBankNote($identifier);
            
            return ['bank_note' => $note];
        } catch (Exception $e) {
            throw new Exception("Bank note verification failed: " . $e->getMessage());
        }
    }
    
    // Send response back to the calling server
   private function sendResponse($success, $message, $data = []) {
    // Clear any previous output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Start a fresh buffer
    ob_start();
    
    // Set headers
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    
    // Get the clean output and clear the buffer
    $output = ob_get_clean();
    
    // Send only the JSON, nothing else
    echo $output;
    exit;
}
}
// API endpoint router
class APIRouter {
    public function route() {
        // Parse the request URI
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Extract the endpoint from the URI
        $base = '/liveaccounts/bankofA/api';
        $endpoint = str_replace($base, '', $uri);
        
        // API endpoints
        switch ($endpoint) {
            case '/sync.php':
                if ($method === 'POST') {
                    $syncAPI = new SyncAPI();
                    $syncAPI->handleRequest();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/auth.php':
                if ($method === 'POST') {
                    $this->handleAuth();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/register.php':
                if ($method === 'POST') {
                    $this->handleRegister();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/account.php':
                if ($method === 'POST') {
                    $this->handleGetAccount();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/transactions.php':
                if ($method === 'POST') {
                    $this->handleGetTransactions();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/balance.php':
                if ($method === 'POST') {
                    $this->handleUpdateBalance();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/bank-notes/create.php':
                if ($method === 'POST') {
                    $this->handleCreateBankNote();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/bank-notes/verify.php':
                if ($method === 'POST') {
                    $this->handleVerifyBankNote();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/bank-notes/deposit.php':
                if ($method === 'POST') {
                    $this->handleDepositBankNote();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/bank-notes/created.php':
                if ($method === 'POST') {
                    $this->handleGetCreatedNotes();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/bank-notes/received.php':
                if ($method === 'POST') {
                    $this->handleGetReceivedNotes();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/bank-notes/receipt.php':
                if ($method === 'POST') {
                    $this->handleGenerateReceipt();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/bank-notes/import.php':
                if ($method === 'POST') {
                    $this->handleImportBankNote();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            case '/bank-notes/download-receipt.php':
                if ($method === 'GET') {
                    $this->handleDownloadReceipt();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
                
            default:
                $this->sendError(404, 'Not Found');
                break;
        }
    }
    
    // Handle authentication
    private function handleAuth() {
        $data = $this->getRequestData();
        
        // Validate request data
        if (!isset($data['username']) || !isset($data['password'])) {
            $this->sendResponse(false, 'Missing username or password');
            return;
        }
        
        try {
            $userAccount = new UserAccount();
            $session = $userAccount->authenticate($data['username'], $data['password']);
            
            if ($session) {
                $this->sendResponse(true, 'Authentication successful', [
                    'session' => $session['session_id'],
                    'expiry' => $session['expiry'],
                    'user_id' => $session['user_id']
                ]);
            } else {
                $this->sendResponse(false, 'Authentication failed');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle user registration
    private function handleRegister() {
        $data = $this->getRequestData();
        
        // Validate request data
        if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
            $this->sendResponse(false, 'Missing required fields');
            return;
try {
            $userAccount = new UserAccount();
            $userId = $userAccount->createUser(
                $data['username'],
                $data['email'],
                $data['password'],
                $data['profile'] ?? []
            );
            
            $this->sendResponse(true, 'Registration successful', [
                'user_id' => $userId
            ]);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    }
    // Handle getting account information
    private function handleGetAccount() {
        $data = $this->getRequestData();
        $userId = $this->authenticateRequest();
        
        if (!$userId) {
            $this->sendResponse(false, 'Authentication required');
            return;
        }
        
        try {
            $userAccount = new UserAccount();
            $accountInfo = $userAccount->getAccountInfo($userId);
            
            $this->sendResponse(true, 'Account info retrieved', [
                'account' => $accountInfo
            ]);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle getting transaction history
    private function handleGetTransactions() {
        $data = $this->getRequestData();
        $userId = $this->authenticateRequest();
        
        if (!$userId) {
            $this->sendResponse(false, 'Authentication required');
            return;
        }
        
        try {
            $userAccount = new UserAccount();
            $transactions = $userAccount->getTransactions($userId);
            
            $this->sendResponse(true, 'Transactions retrieved', [
                'transactions' => $transactions
            ]);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle updating account balance
    private function handleUpdateBalance() {
        $data = $this->getRequestData();
        $userId = $this->authenticateRequest();
        
        if (!$userId) {
            $this->sendResponse(false, 'Authentication required');
            return;
        }
        
        // Validate request data
        if (!isset($data['amount']) || !isset($data['transaction_id'])) {
            $this->sendResponse(false, 'Missing required fields');
            return;
        }
        
        try {
            $userAccount = new UserAccount();
            $result = $userAccount->updateBalance(
                $userId,
                $data['amount'],
                $data['transaction_id'],
                $data['transaction_type'] ?? 'adjustment',
                $data['reference'] ?? ''
            );
            
            $this->sendResponse(true, 'Balance updated', $result);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle creating a bank note
    private function handleCreateBankNote() {
        $data = $this->getRequestData();
        $userId = $this->authenticateRequest();
        
        if (!$userId) {
            $this->sendResponse(false, 'Authentication required');
            return;
        }
        
        // Validate request data
        if (!isset($data['amount']) || !isset($data['note_id']) || !isset($data['serial_number'])) {
            $this->sendResponse(false, 'Missing required fields');
            return;
        }
        
        try {
            $bankNote = new BankNote();
            $note = $bankNote->createBankNote(
                $data['note_id'],
                $data['serial_number'],
                $data['amount'],
                $data['expiry_days'] ?? 30,
                $userId
            );
            
            $this->sendResponse(true, 'Bank note created', $note);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle verifying a bank note
    private function handleVerifyBankNote() {
        $data = $this->getRequestData();
        
        // Validate request data
        if (!isset($data['note_identifier'])) {
            $this->sendResponse(false, 'Missing note identifier');
            return;
        }
        
        try {
            $bankNote = new BankNote();
            $note = $bankNote->verifyBankNote($data['note_identifier']);
            
            $this->sendResponse(true, 'Bank note verified', [
                'bank_note' => $note
            ]);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
private function handleDepositBankNote() {
    $data = $this->getRequestData();
    $userId = $this->authenticateRequest();
    
    if (!$userId) {
        $this->sendResponse(false, 'Authentication required');
        return;
    }
    
    // Validate request data
    if (!isset($data['note_identifier'])) {
        $this->sendResponse(false, 'Missing note identifier');
        return;
    }
    
    // Check for idempotency key
    $idempotencyKey = $data['idempotency_key'] ?? null;
    if ($idempotencyKey) {
        // Check if we've already processed this request
        $db = (new Database())->getConnection();
        $stmt = $db->prepare(
            "SELECT 1 FROM processed_requests 
             WHERE idempotency_key = :key AND user_id = :user_id
             LIMIT 1"
        );
        $stmt->execute([
            ':key' => $idempotencyKey,
            ':user_id' => $userId
        ]);
        
        if ($stmt->rowCount() > 0) {
            // Request already processed, return success
            $this->sendResponse(true, 'Bank note already deposited');
            return;
        }
        
        // Store the request as being processed
        $stmt = $db->prepare(
            "INSERT INTO processed_requests (idempotency_key, user_id, created_at)
             VALUES (:key, :user_id, :created_at)"
        );
        $stmt->execute([
            ':key' => $idempotencyKey,
            ':user_id' => $userId,
            ':created_at' => time()
        ]);
    }
        
        try {
            $bankNote = new BankNote();
            $result = $bankNote->depositBankNote($data['note_identifier'], $userId);
            
            $this->sendResponse(true, 'Bank note deposited', [
                'bank_note' => $result
            ]);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle getting created bank notes
    private function handleGetCreatedNotes() {
        $data = $this->getRequestData();
        $userId = $this->authenticateRequest();
        
        if (!$userId) {
            $this->sendResponse(false, 'Authentication required');
            return;
        }
        
        try {
            $bankNote = new BankNote();
            $notes = $bankNote->getCreatedNotes($userId, $data['status'] ?? null);
            
            $this->sendResponse(true, 'Created bank notes retrieved', [
                'bank_notes' => $notes
            ]);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle getting received bank notes
    private function handleGetReceivedNotes() {
        $data = $this->getRequestData();
        $userId = $this->authenticateRequest();
        
        if (!$userId) {
            $this->sendResponse(false, 'Authentication required');
            return;
        }
        
        try {
            $bankNote = new BankNote();
            $notes = $bankNote->getReceivedNotes($userId, $data['status'] ?? null);
            
            $this->sendResponse(true, 'Received bank notes retrieved', [
                'bank_notes' => $notes
            ]);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle generating a receipt for a bank note
    private function handleGenerateReceipt() {
        $data = $this->getRequestData();
        $userId = $this->authenticateRequest();
        
        if (!$userId) {
            $this->sendResponse(false, 'Authentication required');
            return;
        }
        
        // Validate request data
        if (!isset($data['note_id'])) {
            $this->sendResponse(false, 'Missing note ID');
            return;
        }
        
        try {
            $bankNote = new BankNote();
            $receipt = $bankNote->generateReceipt($data['note_id'], $userId);
            
            $this->sendResponse(true, 'Receipt generated', [
                'receipt' => $receipt
            ]);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle importing a bank note
    private function handleImportBankNote() {
        $userId = $this->authenticateRequest();
        
        if (!$userId) {
            $this->sendResponse(false, 'Authentication required');
            return;
        }
        
        // Check if this is a file upload or JSON input
        $noteJson = null;
        
        if (isset($_FILES['note_file']) && $_FILES['note_file']['error'] == UPLOAD_ERR_OK) {
            // File upload
            $noteJson = file_get_contents($_FILES['note_file']['tmp_name']);
        } else {
            // JSON input
            $data = $this->getRequestData();
            if (isset($data['note_json'])) {
                $noteJson = $data['note_json'];
            }
        }
        
        if (!$noteJson) {
            $this->sendResponse(false, 'No bank note data provided');
            return;
        }
        
        try {
            $bankNote = new BankNote();
            $importedNote = $bankNote->importBankNote($noteJson);
            
            $this->sendResponse(true, 'Bank note imported successfully', [
                'bank_note' => $importedNote,
                'action' => 'deposit'
            ]);
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Handle downloading a receipt
    private function handleDownloadReceipt() {
        $userId = $this->authenticateRequest();
        
        if (!$userId) {
            $this->sendResponse(false, 'Authentication required');
            return;
        }
        
        $noteId = $_GET['id'] ?? '';
        
        if (empty($noteId)) {
            $this->sendResponse(false, 'Missing note ID');
            return;
        }
        
        try {
            $bankNote = new BankNote();
            $receipt = $bankNote->generateReceipt($noteId, $userId);
            
            // Generate QR code data
            $qrData = json_encode([
                'note_id' => $receipt['note_id'],
                'serial' => $receipt['serial'],
                'amount' => $receipt['amount'],
                'issuer' => $receipt['issuer'],
                'date' => $receipt['date'],
                'expiry' => $receipt['expiry'],
                'status' => $receipt['status'],
                'transaction_id' => $receipt['transaction_id'] ?? null
            ]);
            
            // Generate QR code URL using Google Charts API
            $qrCodeUrl = 'https://chart.googleapis.com/chart?' . http_build_query([
                'cht' => 'qr',
                'chs' => '300x300',
                'chl' => $qrData,
                'choe' => 'UTF-8'
            ]);
            
            // Output HTML page with receipt and QR code
            header('Content-Type: text/html');
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Bank Note Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
                    .receipt { border: 1px solid #ccc; padding: 20px; }
                    .receipt-header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
                    .qr-code { text-align: center; margin: 20px 0; }
                    .serial { text-align: center; font-family: monospace; font-size: 16px; margin: 10px 0; }
                    .receipt-row { display: flex; margin: 5px 0; }
                    .receipt-label { font-weight: bold; width: 40%; }
                    .receipt-value { width: 60%; }
                    .print-button { display: block; margin: 20px auto; padding: 10px 20px; background: #1a5276; color: white; border: none; border-radius: 4px; cursor: pointer; }
                    @media print { .print-button { display: none; } }
                </style>
            </head>
            <body>
                <div class="receipt">
                    <div class="receipt-header">
                        <h1>SecureBank Network</h1>
                        <h2>Bank Note Receipt</h2>
                    </div>
                    
                    <div class="qr-code">
                        <img src="' . $qrCodeUrl . '" alt="Receipt QR Code">
                    </div>
                    
                    <div class="serial">
                        Serial: ' . htmlspecialchars($receipt['serial']) . '
                    </div>
                    
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
                        <div class="receipt-label">Issuer:</div>
                        <div class="receipt-value">' . htmlspecialchars($receipt['issuer']) . '</div>
                    </div>
                    
                    <div class="receipt-row">
                        <div class="receipt-label">Expiry:</div>
                        <div class="receipt-value">' . date('Y-m-d H:i:s', strtotime($receipt['expiry'])) . '</div>
                    </div>
                    
                    <div class="receipt-row">
                        <div class="receipt-label">Status:</div>
                        <div class="receipt-value">' . strtoupper($receipt['status']) . '</div>
                    </div>';
            
            // Add redemption info if available
            if (isset($receipt['redeemed_at'])) {
                echo '
                    <div class="receipt-row">
                        <div class="receipt-label">Redeemed At:</div>
                        <div class="receipt-value">' . date('Y-m-d H:i:s', strtotime($receipt['redeemed_at'])) . '</div>
                    </div>
                    
                    <div class="receipt-row">
                        <div class="receipt-label">Redeemed By:</div>
                        <div class="receipt-value">' . htmlspecialchars($receipt['redeemed_by']) . '</div>
                    </div>';
            }
            
            echo '
                </div>
                
                <button class="print-button" onclick="window.print()">Print Receipt</button>
            </body>
            </html>';
            
            exit;
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage());
        }
    }
    
    // Get JSON request data
    private function getRequestData() {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }
    
    // Authenticate request using Bearer token
    private function authenticateRequest() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            return false;
        }
        
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
                return $session['user_id'];
            }
        }
        
        return false;
    }
    
    // Send JSON response
    private function sendResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        
        echo json_encode($response);
        exit;
    }
    
    // Send error response
    private function sendError($code, $message) {
        http_response_code($code);
        $this->sendResponse(false, $message);
    }
}

// Initialize the router and process the request
try {
    $router = new APIRouter();
    $router->route();
} catch (Exception $e) {
    // Catch any uncaught exceptions
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}