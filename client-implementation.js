// SecureBank Client Library
const SecureBank = (function() {
    // Private configuration
    const config = {
        // Primary server endpoint (can be changed dynamically based on availability)
        apiEndpoint: 'https://jcmc.serveminecraft.net/liveaccounts/bankofA/api',
        
        // Backup server endpoints (used if primary fails)
        backupEndpoints: [
            'https://jcmc.serveminecraft.net/liveaccounts/bankofB/api',
            'https://jcmc.serveminecraft.net/liveaccounts/bankofC/api'
        ],
        
        // Request timeout in milliseconds
        requestTimeout: 30000,
        
        // Session storage key
        sessionKey: 'secure_bank_session',
        
        // Current active endpoint (starts with primary)
        currentEndpoint: 'https://jcmc.serveminecraft.net/liveaccounts/bankofA/api'
    };
    
    // Private session data
    let session = {
        id: null,
        expiry: null,
        userId: null
    };
    
    // Private methods
    
    // Makes an AJAX request to the server
const makeRequest = function(endpoint, data, successCallback, errorCallback, retryCount = 0) {
    // Create XMLHttpRequest object
    const xhr = new XMLHttpRequest();
    
    // Setup timeout
    xhr.timeout = config.requestTimeout;
    
    // Setup handlers
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                // Log the raw response for debugging
                console.log(`Response from ${endpoint}:`, xhr.responseText);
                
                // Try to parse as JSON
                const response = JSON.parse(xhr.responseText);
                successCallback(response);
            } catch (e) {
                console.error(`JSON parsing error for ${endpoint}:`, e);
                console.error(`Raw response:`, xhr.responseText);
                errorCallback(`Invalid response format: ${e.message}`);
            }
        } else {
            console.warn(`HTTP error ${xhr.status} from ${endpoint}`);
            handleRequestError(endpoint, data, successCallback, errorCallback, retryCount);
        }
    };
        
        xhr.onerror = function() {
            handleRequestError(endpoint, data, successCallback, errorCallback, retryCount);
        };
        
        xhr.ontimeout = function() {
            handleRequestError(endpoint, data, successCallback, errorCallback, retryCount);
        };
        
        // Open connection and send request
        xhr.open('POST', config.currentEndpoint + endpoint, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        // Add session token if available
        if (session.id) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + session.id);
        }
        
        xhr.send(JSON.stringify(data));
    };
    
    // Handles request errors by trying backup servers
    const handleRequestError = function(endpoint, data, successCallback, errorCallback, retryCount) {
        // Max retry attempts
        const maxRetries = config.backupEndpoints.length;
        
        if (retryCount < maxRetries) {
            // Switch to a backup endpoint
            config.currentEndpoint = config.backupEndpoints[retryCount];
            
            // Retry the request
            makeRequest(endpoint, data, successCallback, errorCallback, retryCount + 1);
        } else {
            // All endpoints failed
            errorCallback('Unable to connect to any server');
            
            // Reset to primary endpoint for next attempt
            config.currentEndpoint = config.apiEndpoint;
        }
    };
    
    // Saves the session to localStorage
    const saveSession = function() {
        if (session.id) {
            localStorage.setItem(config.sessionKey, JSON.stringify({
                id: session.id,
                expiry: session.expiry,
                userId: session.userId
            }));
        }
    };
    
    // Loads the session from localStorage
    const loadSession = function() {
        const savedSession = localStorage.getItem(config.sessionKey);
        
        if (savedSession) {
            try {
                const parsedSession = JSON.parse(savedSession);
                
                // Check if session is expired
                if (parsedSession.expiry && parsedSession.expiry > Date.now() / 1000) {
                    session.id = parsedSession.id;
                    session.expiry = parsedSession.expiry;
                    session.userId = parsedSession.userId;
                    return true;
                } else {
                    // Session expired, clear it
                    clearSession();
                }
            } catch (e) {
                clearSession();
            }
        }
        
        return false;
    };
    
    // Clears the session
    const clearSession = function() {
        session.id = null;
        session.expiry = null;
        session.userId = null;
        localStorage.removeItem(config.sessionKey);
    };
    
    // Generates a unique transaction ID
    const generateTransactionId = function() {
        return 'txn_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    };
    
    // Public API
    return {
        // Initialize the client library
        init: function(options = {}) {
            // Override default config with provided options
            if (options.apiEndpoint) {
                config.apiEndpoint = options.apiEndpoint;
                config.currentEndpoint = options.apiEndpoint;
            }
            
            if (options.backupEndpoints) {
                config.backupEndpoints = options.backupEndpoints;
            }
            
            if (options.requestTimeout) {
                config.requestTimeout = options.requestTimeout;
            }
            
            // Load session if available
            loadSession();
            
            // Return initialization status
            return {
                initialized: true,
                isLoggedIn: !!session.id
            };
        },
        
        // Register a new user
        register: function(userData, successCallback, errorCallback) {
            // Validate required fields
            if (!userData.username || !userData.email || !userData.password) {
                errorCallback('Missing required fields');
                return;
            }
            
            // Prepare profile data (additional user information)
            const profileData = {
                fullName: userData.fullName || '',
                address: userData.address || '',
                phone: userData.phone || '',
                preferences: userData.preferences || {}
            };
            
            // Prepare request data
            const requestData = {
                username: userData.username,
                email: userData.email,
                password: userData.password,
                profile: profileData
            };
            
            // Make the request
            makeRequest(
                '/register.php',
                requestData,
                function(response) {
                    if (response.success) {
                        successCallback(response);
                    } else {
                        errorCallback(response.message || 'Registration failed');
                    }
                },
                errorCallback
            );
        },
        
        // Authenticate a user
        login: function(username, password, successCallback, errorCallback) {
            // Validate credentials
            if (!username || !password) {
                errorCallback('Username and password are required');
                return;
            }
            
            // Prepare request data
            const requestData = {
                username: username,
                password: password
            };
            
            // Make the request
            makeRequest(
                '/auth.php',
                requestData,
                function(response) {
                    if (response.success && response.session) {
                        // Store session
                        session.id = response.session;
                        session.expiry = response.expiry;
                        session.userId = response.user_id;
                        
                        // Save to localStorage
                        saveSession();
                        
                        successCallback({
                            userId: session.userId,
                            expiry: new Date(session.expiry * 1000).toISOString()
                        });
                    } else {
                        errorCallback(response.message || 'Authentication failed');
                    }
                },
                errorCallback
            );
        },
        
        // Logout the current user
        logout: function() {
            clearSession();
            return true;
        },
        
        // Check if user is logged in
        isLoggedIn: function() {
            return !!session.id && session.expiry > Date.now() / 1000;
        },
        
        // Get user account information
        getAccountInfo: function(successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Make the request
            makeRequest(
                '/account.php',
                { user_id: session.userId },
                function(response) {
                    if (response.success) {
                        successCallback(response.account);
                    } else {
                        errorCallback(response.message || 'Failed to retrieve account information');
                    }
                },
                errorCallback
            );
        },
        
        // Update account balance (for demonstration purposes)
        updateBalance: function(amount, successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Generate a unique transaction ID
            const transactionId = generateTransactionId();
            
            // Make the request
            makeRequest(
                '/balance.php',
                {
                    user_id: session.userId,
                    amount: amount,
                    transaction_id: transactionId
                },
                function(response) {
                    if (response.success) {
                        successCallback(response);
                    } else {
                        errorCallback(response.message || 'Failed to update balance');
                    }
                },
                errorCallback
            );
        },
        
        // Update user profile information
        updateProfile: function(profileData, successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Make the request
            makeRequest(
                '/profile.php',
                {
                    user_id: session.userId,
                    profile: profileData
                },
                function(response) {
                    if (response.success) {
                        successCallback(response);
                    } else {
                        errorCallback(response.message || 'Failed to update profile');
                    }
                },
                errorCallback
            );
        },
        
        // Send a secure message to another user
        sendMessage: function(recipientId, message, successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Make the request
            makeRequest(
                '/message.php',
                {
                    sender_id: session.userId,
                    recipient_id: recipientId,
                    message: message,
                    timestamp: Math.floor(Date.now() / 1000)
                },
                function(response) {
                    if (response.success) {
                        successCallback(response);
                    } else {
                        errorCallback(response.message || 'Failed to send message');
                    }
                },
                errorCallback
            );
        },
        
        // Set the primary server endpoint
        setServerEndpoint: function(endpoint) {
            if (endpoint) {
                config.apiEndpoint = endpoint;
                config.currentEndpoint = endpoint;
                return true;
            }
            return false;
        },
        
        // Set backup server endpoints
        setBackupEndpoints: function(endpoints) {
            if (Array.isArray(endpoints) && endpoints.length > 0) {
                config.backupEndpoints = endpoints;
                return true;
            }
            return false;
        },
        
        // Create a bank note
        createBankNote: function(amount, expiryDays, successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Generate unique IDs
            const noteId = 'note_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const serialNumber = 'SB' + Date.now().toString().substr(-10) + Math.random().toString(36).substr(2, 6).toUpperCase();
            
            // Make the request
            makeRequest(
                '/bank-notes/create.php',
                {
                    note_id: noteId,
                    serial_number: serialNumber,
                    amount: amount,
                    expiry_days: expiryDays
                },
                function(response) {
                    if (response.success) {
                        successCallback(response);
                    } else {
                        errorCallback(response.message || 'Failed to create bank note');
                    }
                },
                errorCallback
            );
        },
        
        // Verify a bank note
        verifyBankNote: function(noteIdentifier, successCallback, errorCallback) {
            // Make the request
            makeRequest(
                '/bank-notes/verify.php',
                { note_identifier: noteIdentifier },
                function(response) {
                    if (response.success) {
                        successCallback(response.bank_note);
                    } else {
                        errorCallback(response.message || 'Failed to verify bank note');
                    }
                },
                errorCallback
            );
        },
        
        // Deposit a bank note
        depositBankNote: function(noteIdentifier, successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Make the request
            makeRequest(
                '/bank-notes/deposit.php',
                { note_identifier: noteIdentifier },
                function(response) {
                    if (response.success) {
                        successCallback(response);
                    } else {
                        errorCallback(response.message || 'Failed to deposit bank note');
                    }
                },
                errorCallback
            );
        },
        
        // Get created bank notes
        getCreatedNotes: function(status, successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Make the request
            makeRequest(
                '/bank-notes/created.php',
                { status: status || 'all' },
                function(response) {
                    if (response.success) {
                        successCallback(response.bank_notes);
                    } else {
                        errorCallback(response.message || 'Failed to retrieve created notes');
                    }
                },
                errorCallback
            );
        },
        
        // Get received bank notes
        getReceivedNotes: function(status, successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Make the request
            makeRequest(
                '/bank-notes/received.php',
                { status: status || 'all' },
                function(response) {
                    if (response.success) {
                        successCallback(response.bank_notes);
                    } else {
                        errorCallback(response.message || 'Failed to retrieve received notes');
                    }
                },
                errorCallback
            );
        },
        
        // Generate a receipt for a bank note
        generateReceipt: function(noteId, successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Make the request
            makeRequest(
                '/bank-notes/receipt.php',
                { note_id: noteId },
                function(response) {
                    if (response.success) {
                        successCallback(response.receipt);
                    } else {
                        errorCallback(response.message || 'Failed to generate receipt');
                    }
                },
                errorCallback
            );
        },
        
        // Import a bank note from JSON
        importBankNote: function(noteJson, successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Make the request
            makeRequest(
                '/bank-notes/import.php',
                { note_json: noteJson },
                function(response) {
                    if (response.success) {
                        successCallback(response);
                    } else {
                        errorCallback(response.message || 'Failed to import bank note');
                    }
                },
                errorCallback
            );
        },
        
        // Get transaction history
        getTransactions: function(successCallback, errorCallback) {
            // Check if user is logged in
            if (!this.isLoggedIn()) {
                errorCallback('Not authenticated');
                return;
            }
            
            // Make the request
            makeRequest(
                '/transactions.php',
                {},
                function(response) {
                    if (response.success) {
                        successCallback(response.transactions);
                    } else {
                        errorCallback(response.message || 'Failed to retrieve transactions');
                    }
                },
                errorCallback
            );
        }
    };
})();

// HTML Interface 
document.addEventListener('DOMContentLoaded', function() {
    // Initialize SecureBank client
    SecureBank.init();
    
    // Check login status
    if (SecureBank.isLoggedIn()) {
        showDashboard();
    } else {
        showLoginForm();
    }
    
    // Login form handler
    document.getElementById('login-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        SecureBank.login(username, password, function(response) {
            document.getElementById('login-error').textContent = '';
            showDashboard();
        }, function(error) {
            document.getElementById('login-error').textContent = error;
        });
    });
    
    // Register form handler
    document.getElementById('register-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const userData = {
            username: document.getElementById('reg-username').value,
            email: document.getElementById('reg-email').value,
            password: document.getElementById('reg-password').value,
            fullName: document.getElementById('reg-fullname').value,
            phone: document.getElementById('reg-phone').value
        };
        
        SecureBank.register(userData, function(response) {
            document.getElementById('register-error').textContent = '';
            alert('Registration successful! Please log in.');
            showLoginForm();
        }, function(error) {
            document.getElementById('register-error').textContent = error;
        });
    });
    
    // Logout button handler
    document.getElementById('logout-button')?.addEventListener('click', function() {
        SecureBank.logout();
        showLoginForm();
    });
    
    // Functions to show/hide forms
    function showLoginForm() {
        document.getElementById('login-container').style.display = 'block';
        document.getElementById('register-container').style.display = 'none';
        document.getElementById('dashboard-container').style.display = 'none';
    }
    
    function showRegisterForm() {
        document.getElementById('login-container').style.display = 'none';
        document.getElementById('register-container').style.display = 'block';
        document.getElementById('dashboard-container').style.display = 'none';
    }
    
    function showDashboard() {
        document.getElementById('login-container').style.display = 'none';
        document.getElementById('register-container').style.display = 'none';
        document.getElementById('dashboard-container').style.display = 'block';
        
        // Load account information
        SecureBank.getAccountInfo(function(accountInfo) {
            document.getElementById('account-balance').textContent = 
                accountInfo.balance ? '$' + accountInfo.balance.toFixed(2) : '$0.00';
            document.getElementById('account-name').textContent = 
                accountInfo.profile.fullName || accountInfo.username;
        }, function(error) {
            console.error('Error loading account info:', error);
        });
    }
    
    // Toggle between login and register forms
    document.getElementById('show-register')?.addEventListener('click', function(e) {
        e.preventDefault();
        showRegisterForm();
    });
    
    document.getElementById('show-login')?.addEventListener('click', function(e) {
        e.preventDefault();
        showLoginForm();
    });
    
    // Transfer funds form
    document.getElementById('transfer-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const amount = parseFloat(document.getElementById('transfer-amount').value);
        
        if (isNaN(amount) || amount <= 0) {
            document.getElementById('transfer-error').textContent = 'Please enter a valid amount';
            return;
        }
        
        SecureBank.updateBalance(amount, function(response) {
            document.getElementById('transfer-error').textContent = '';
            alert('Transfer successful!');
            
            // Refresh account info
            SecureBank.getAccountInfo(function(accountInfo) {
                document.getElementById('account-balance').textContent = 
                    accountInfo.balance ? '$' + accountInfo.balance.toFixed(2) : '$0.00';
            }, function(error) {
                console.error('Error refreshing account info:', error);
            });
        }, function(error) {
            document.getElementById('transfer-error').textContent = error;
        });
    });
    
    // Bank note creation form
    document.getElementById('withdraw-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const amount = parseFloat(document.getElementById('withdraw-amount').value);
        const expiryDays = parseInt(document.getElementById('withdraw-expiry').value);
        
        if (isNaN(amount) || amount <= 0) {
            document.getElementById('withdraw-error').textContent = 'Please enter a valid amount';
            return;
        }
        
        SecureBank.createBankNote(amount, expiryDays, function(response) {
            document.getElementById('withdraw-error').textContent = '';
            
            // Display bank note
            document.getElementById('banknote-serial').textContent = response.serial;
            document.getElementById('banknote-amount').textContent = '$' + response.amount.toFixed(2);
            document.getElementById('banknote-issuer').textContent = response.issuer;
            document.getElementById('banknote-issued').textContent = new Date(response.issued_at).toLocaleString();
            document.getElementById('banknote-expires').textContent = new Date(response.expires_at).toLocaleString();
            
            document.getElementById('bank-note-container').style.display = 'block';
            
            // Refresh account info
            SecureBank.getAccountInfo(function(accountInfo) {
                document.getElementById('account-balance').textContent = 
                    accountInfo.balance ? '$' + accountInfo.balance.toFixed(2) : '$0.00';
            }, function(error) {
                console.error('Error refreshing account info:', error);
            });
        }, function(error) {
            document.getElementById('withdraw-error').textContent = error;
        });
    });
    
    // Bank note deposit form
    document.getElementById('deposit-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const noteIdentifier = document.getElementById('note-id').value;
        
        if (!noteIdentifier) {
            document.getElementById('deposit-error').textContent = 'Please enter a bank note ID';
            return;
        }
        
        SecureBank.depositBankNote(noteIdentifier, function(response) {
            document.getElementById('deposit-error').textContent = '';
            document.getElementById('deposit-success').textContent = 'Bank note deposited successfully!';
            document.getElementById('deposit-success').style.display = 'block';
            
            // Refresh account info
            SecureBank.getAccountInfo(function(accountInfo) {
                document.getElementById('account-balance').textContent = 
                    accountInfo.balance ? '$' + accountInfo.balance.toFixed(2) : '$0.00';
            }, function(error) {
                console.error('Error refreshing account info:', error);
            });
        }, function(error) {
            document.getElementById('deposit-error').textContent = error;
            document.getElementById('deposit-error').style.display = 'block';
            document.getElementById('deposit-success').style.display = 'none';
        });
    });
});