<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Distributed Banking System</title>
    <style>
        :root {
            --primary-color: #1a5276;
            --secondary-color: #2980b9;
            --success-color: #27ae60;
            --danger-color: #c0392b;
            --warning-color: #f39c12;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .logo span {
            color: var(--light-color);
        }
        
        .bank-selector {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 5px 10px;
            border-radius: 4px;
            margin-left: 20px;
            font-size: 0.9rem;
        }
        
        .bank-selector select {
            background-color: transparent;
            color: white;
            border: none;
            outline: none;
            font-size: 0.9rem;
        }
        
        .bank-selector select option {
            background-color: var(--primary-color);
            color: white;
        }
        
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 15px;
        }
        
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: var(--danger-color);
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: var(--success-color);
            border: 1px solid #c3e6cb;
        }
        
        .toggle-form {
            color: var(--secondary-color);
            text-decoration: underline;
            cursor: pointer;
        }
        
        .dashboard-container {
            display: none;
            margin: 30px auto;
        }
        
        .dashboard-header {
            background-color: var(--light-color);
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-balance {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .dashboard-name {
            font-size: 1.2rem;
            color: var(--dark-color);
        }
        
        .card {
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .transaction-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .transaction-date {
            color: #777;
            font-size: 0.9rem;
        }
        
        .transaction-amount {
            font-weight: bold;
        }
        
        .amount-positive {
            color: var(--success-color);
        }
        
        .amount-negative {
            color: var(--danger-color);
        }
        
        .bank-note-container {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
            background-color: #f9f9f9;
        }
        
        .bank-note {
            background: linear-gradient(to right, #f1f9ff, #e8f4fc);
            border: 1px solid #c0d6e4;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            position: relative;
            font-family: 'Courier New', monospace;
            text-align: left;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .bank-note-header {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            color: var(--primary-color);
            border-bottom: 1px solid #c0d6e4;
            padding-bottom: 5px;
        }
        
        .bank-note-body {
            margin-bottom: 10px;
        }
        
        .bank-note-footer {
            font-size: 0.8rem;
            color: #666;
            text-align: center;
        }
        
        .bank-note-serial {
            position: absolute;
            top: 5px;
            right: 10px;
            font-size: 0.8rem;
            color: var(--primary-color);
        }
        
        .bank-note-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            color: rgba(41, 128, 185, 0.1);
            font-weight: bold;
            pointer-events: none;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .loading {
            position: relative;
        }
        
        .loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-active {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-redeemed {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .status-expired {
            background-color: var(--danger-color);
            color: white;
        }
        
        .bank-note-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }
        
        /* Enhanced styles for bank note management */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
        }
        
        .tab {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .tab:hover:not(.active) {
            background-color: #f5f5f5;
            border-bottom: 2px solid #ddd;
        }
        
        .tab-content {
            padding: 10px 0;
        }
        
        .note-filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .notes-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .note-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            background-color: #fff;
            transition: all 0.3s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .note-item:hover {
            box-shadow: 0 3px 5px rgba(0,0,0,0.2);
        }
        
        .note-header {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f9f9f9;
            border-radius: 5px 5px 0 0;
        }
        
        .note-serial {
            font-family: monospace;
            font-size: 0.9rem;
            color: #666;
        }
        
        .note-body {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .note-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .note-details {
            font-size: 0.9rem;
            color: #666;
        }
        
        .note-actions {
            padding: 10px 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #999;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 10% auto;
            padding: 0;
            border-radius: 5px;
            width: 80%;
            max-width: 700px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: modalopen 0.3s;
        }
        
        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-10px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .modal-header {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .close-modal {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: var(--primary-color);
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        /* Bank Note Receipt Styles */
        .bank-note-receipt {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #fff;
            font-family: 'Courier New', Courier, monospace;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .receipt-logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .receipt-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .receipt-body {
            margin-bottom: 20px;
        }
        
        .receipt-row {
            display: flex;
            padding: 5px 0;
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
            border-top: 1px solid #000;
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
        
        /* Drag and drop area */
        .drag-drop-area {
            border: 2px dashed #ddd;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            transition: all 0.3s;
        }
        
        .drag-drop-area.dragover {
            border-color: var(--primary-color);
            background-color: rgba(41, 128, 185, 0.1);
        }
        
        .drag-drop-area p {
            margin: 0;
            color: #666;
        }
        
        /* Modified deposit section to support drag and drop */
        #deposit-container {
            padding: 20px;
            margin-bottom: 20px;
        }
        
        #upload-or-paste {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            min-height: 150px;
            resize: vertical;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">Secure<span>Bank</span> <span class="bank-selector">Network: 
                    <select id="bank-selector">
                        <option value="bankofA">Bank of A</option>
                        <option value="bankofB">Bank of B</option>
                        <option value="bankofC">Bank of C</option>
                        <option value="regionalcredit">Regional Credit</option>
                    </select>
                </span></div>
                <div id="header-auth-status"></div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Login Form -->
        <div id="login-container" class="auth-container">
            <h2>Login to Your Account</h2>
            <div id="login-error" class="alert alert-danger" style="display: none;"></div>
            
            <form id="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-block">Login</button>
            </form>
            
            <div class="text-center mt-3">
                Don't have an account? <span id="show-register" class="toggle-form">Register now</span>
            </div>
        </div>
        
        <!-- Registration Form -->
        <div id="register-container" class="auth-container" style="display: none;">
            <h2>Create a New Account</h2>
            <div id="register-error" class="alert alert-danger" style="display: none;"></div>
            
            <form id="register-form">
                <div class="form-group">
                    <label for="reg-username">Username</label>
                    <input type="text" id="reg-username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" required minlength="8">
                    <small>Password must be at least 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="reg-fullname">Full Name</label>
                    <input type="text" id="reg-fullname" name="fullname">
                </div>
                
                <div class="form-group">
                    <label for="reg-phone">Phone Number</label>
                    <input type="tel" id="reg-phone" name="phone">
                </div>
                
                <button type="submit" class="btn btn-block">Register</button>
            </form>
            
            <div class="text-center mt-3">
                Already have an account? <span id="show-login" class="toggle-form">Login</span>
            </div>
        </div>
        
        <!-- Dashboard -->
        <div id="dashboard-container" class="dashboard-container">
            <div class="dashboard-header">
                <div>
                    <div class="dashboard-name">Welcome, <span id="account-name">User</span></div>
                    <div class="dashboard-balance">Balance: <span id="account-balance">$0.00</span></div>
                </div>
                <button id="logout-button" class="btn">Logout</button>
            </div>
            
            <div class="grid">
                <div class="card">
                    <div class="card-title">Withdraw Bank Note</div>
                    <div id="withdraw-error" class="alert alert-danger" style="display: none;"></div>
                    
                    <form id="withdraw-form">
                        <div class="form-group">
                            <label for="withdraw-amount">Amount</label>
                            <input type="number" id="withdraw-amount" name="amount" step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="withdraw-expiry">Expiry (days)</label>
                            <input type="number" id="withdraw-expiry" name="expiry" value="30" min="1" max="90">
                            <small>Bank notes are valid for 1-90 days</small>
                        </div>
                        
                        <button type="submit" class="btn btn-block">Generate Bank Note</button>
                    </form>
                    
                    <div id="bank-note-container" class="bank-note-container" style="display: none;">
                        <h3>Your Bank Note</h3>
                        <p>This note can be redeemed at any bank in the SecureBank network</p>
                        
                        <div class="bank-note">
                            <div class="bank-note-serial">SN: <span id="banknote-serial"></span></div>
                            <div class="bank-note-watermark">$</div>
                            <div class="bank-note-header">SECUREBANK NETWORK - DIGITAL BANK NOTE</div>
                            <div class="bank-note-body">
                                <p><strong>Amount:</strong> <span id="banknote-amount"></span></p>
                                <p><strong>Issuer:</strong> <span id="banknote-issuer"></span></p>
                                <p><strong>Issued:</strong> <span id="banknote-issued"></span></p>
                                <p><strong>Expires:</strong> <span id="banknote-expires"></span></p>
                                <p><strong>Status:</strong> <span id="banknote-status" class="status-badge status-active">ACTIVE</span></p>
                            </div>
                            <div class="bank-note-footer">
                                This note is secured by cryptographic signature and can be validated at any participating bank.
                            </div>
                        </div>
                        
                        <div class="bank-note-actions">
                            <button id="print-note" class="btn">Print Note</button>
                            <button id="copy-note" class="btn">Copy Note ID</button>
                            <button id="new-note" class="btn">New Note</button>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-title">Deposit Bank Note</div>
                    <div id="deposit-error" class="alert alert-danger" style="display: none;"></div>
                    <div id="deposit-success" class="alert alert-success" style="display: none;"></div>
                    
                    <div id="deposit-container">
                        <form id="deposit-form">
                            <div class="form-group">
                                <label for="note-id">Bank Note ID or Serial Number</label>
                                <input type="text" id="note-id" name="note_id" placeholder="Enter bank note ID/serial number" required>
                            </div>
                            
                            <button type="submit" class="btn btn-block">Verify & Deposit</button>
                        </form>
                        
                        <div id="upload-or-paste">
                            <button id="upload-note-btn" class="btn">Upload Bank Note File</button>
                            <button id="paste-note-btn" class="btn">Paste Bank Note</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Account Section with Bank Note Management -->
            <div class="card" id="account-notes-section">
                <div class="card-title">Your Bank Notes</div>
                <div class="tabs">
                    <div class="tab active" data-tab="created-notes">Created Notes</div>
                    <div class="tab" data-tab="received-notes">Received Notes</div>
                </div>
                
                <div class="tab-content" id="created-notes-content">
                    <div class="note-filters">
                        <select id="status-filter-created">
                            <option value="all">All Notes</option>
                            <option value="active">Active</option>
                            <option value="redeemed">Redeemed</option>
                            <option value="expired">Expired</option>
                        </select>
                        <button id="refresh-created-notes" class="btn btn-sm">Refresh</button>
                    </div>
                    
                    <div class="notes-list" id="created-notes-list">
                        <div class="empty-state">
                            <p>You haven't created any bank notes yet.</p>
                            <button class="btn" onclick="document.getElementById('withdraw-form').scrollIntoView()">Create a Bank Note</button>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="received-notes-content" style="display: none;">
                    <div class="note-filters">
                        <select id="status-filter-received">
                            <option value="all">All Notes</option>
                            <option value="active">Active</option>
                            <option value="redeemed">Redeemed</option>
                            <option value="expired">Expired</option>
                        </select>
                        <button id="refresh-received-notes" class="btn btn-sm">Refresh</button>
                    </div>
                    
                    <div class="notes-list" id="received-notes-list">
                        <div class="empty-state">
                            <p>You haven't received any bank notes yet.</p>
                            <button class="btn" onclick="document.getElementById('deposit-form').scrollIntoView()">Deposit a Bank Note</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-title">Recent Transactions</div>
                <div id="transactions-list">
                    <!-- Transactions will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bank Note Receipt Template -->
    <template id="bank-note-receipt-template">
        <div class="bank-note-receipt">
            <div class="receipt-header">
                <div class="receipt-logo">SecureBank Network</div>
                <div class="receipt-title">BANK NOTE RECEIPT</div>
            </div>
            
            <div class="receipt-body">
                <div class="receipt-row">
                    <div class="receipt-label">Transaction ID:</div>
                    <div class="receipt-value" id="receipt-transaction-id"></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Date:</div>
                    <div class="receipt-value" id="receipt-date"></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Note ID:</div>
                    <div class="receipt-value" id="receipt-note-id"></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Serial Number:</div>
                    <div class="receipt-value" id="receipt-serial"></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Amount:</div>
                    <div class="receipt-value" id="receipt-amount"></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Issuing Bank:</div>
                    <div class="receipt-value" id="receipt-issuer"></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Expiry Date:</div>
                    <div class="receipt-value" id="receipt-expiry"></div>
                </div>
                <div class="receipt-row" id="receipt-redemption-row" style="display: none;">
                    <div class="receipt-label">Redeemed At:</div>
                    <div class="receipt-value" id="receipt-redemption"></div>
                </div>
            </div>
            
            <div class="receipt-footer">
                <div class="receipt-notice">Keep this receipt for your records.</div>
                <div class="receipt-signature">Electronically Generated - No Signature Required</div>
            </div>
        </div>
    </template>
    
    <!-- Bank Note Template for Download -->
	<!-- Bank Note Template for Download -->
    <template id="bank-note-file-template">
{
  "securebank_note": {
    "note_id": "{{note_id}}",
    "serial": "{{serial}}",
    "amount": {{amount}},
    "issuer": "{{issuer}}",
    "issuer_id": "{{issuer_id}}",
    "issued_at": "{{issued_at}}",
    "expires_at": "{{expires_at}}",
    "signature": "{{signature}}"
  }
}
    </template>
    
    <!-- Bank Note Item Template -->
    <template id="note-item-template">
        <div class="note-item">
            <div class="note-header">
                <div class="note-serial"></div>
                <div class="note-status"></div>
            </div>
            <div class="note-body">
                <div class="note-amount"></div>
                <div class="note-details">
                    <div><strong>Issued:</strong> <span class="note-issued"></span></div>
                    <div><strong>Expires:</strong> <span class="note-expires"></span></div>
                    <div><strong>Issuer:</strong> <span class="note-issuer"></span></div>
                </div>
            </div>
            <div class="note-actions">
                <button class="btn btn-sm btn-view">View Receipt</button>
                <button class="btn btn-sm btn-download">Download</button>
            </div>
        </div>
    </template>
    
    <!-- Import Bank Note Modal -->
    <div id="import-note-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-modal">&times;</span>
                <h2>Import Bank Note</h2>
            </div>
            <div class="modal-body">
                <div class="tabs">
                    <div class="tab active" data-tab="paste-note">Paste Note</div>
                    <div class="tab" data-tab="upload-note">Upload File</div>
                </div>
                
                <div class="tab-content" id="paste-note-content">
                    <div class="form-group">
                        <label for="note-json">Paste Bank Note JSON</label>
                        <textarea id="note-json" rows="10" placeholder='{"securebank_note": {"note_id": "note_123", "serial": "SB-...", ...}}' required></textarea>
                    </div>
                    <div id="paste-note-error" class="alert alert-danger" style="display: none;"></div>
                    <button id="import-pasted-note" class="btn btn-block">Import Bank Note</button>
                </div>
                
                <div class="tab-content" id="upload-note-content" style="display: none;">
                    <div class="drag-drop-area">
                        <p>Drag and drop bank note file here</p>
                    </div>
                    <div class="form-group">
                        <label for="note-file">Or select a bank note file (.sbnt)</label>
                        <input type="file" id="note-file" accept=".sbnt,application/json" required>
                    </div>
                    <div id="upload-note-error" class="alert alert-danger" style="display: none;"></div>
                    <button id="import-uploaded-note" class="btn btn-block">Import Bank Note</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bank Note Receipt Modal -->
    <div id="receipt-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-modal">&times;</span>
                <h2>Bank Note Receipt</h2>
            </div>
            <div class="modal-body" id="receipt-container">
                <!-- Receipt content will be cloned here -->
            </div>
            <div class="modal-footer">
                <button id="print-receipt" class="btn">Print Receipt</button>
                <button id="download-receipt" class="btn">Download Receipt</button>
                <button id="close-receipt" class="btn">Close</button>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; 2025 SecureBank Network - Distributed Server System</p>
        </div>
    </footer>
    
    <!-- Include the SecureBank Client Library -->
    <script src="client-implementation.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Base API URL for the current bank
            let apiBaseUrl = 'https://jcmc.serveminecraft.net/liveaccounts/bankofA/api';
            
            // Initialize SecureBank client
            SecureBank.init({
                apiEndpoint: apiBaseUrl,
                backupEndpoints: [
                    'https://jcmc.serveminecraft.net/liveaccounts/bankofB/api',
                    'https://jcmc.serveminecraft.net/liveaccounts/bankofC/api'
                ]
            });
            
            // Handle bank selection
            document.getElementById('bank-selector').addEventListener('change', function() {
                const selectedBank = this.value;
                apiBaseUrl = `https://jcmc.serveminecraft.net/liveaccounts/${selectedBank}/api`;
                
                // Update the API endpoint
                SecureBank.setServerEndpoint(apiBaseUrl);
                
                // If user is logged in, refresh account info
                if (SecureBank.isLoggedIn()) {
                    loadAccountInfo();
                }
            });
            
            // Check login status
            if (SecureBank.isLoggedIn()) {
                showDashboard();
                loadAccountInfo();
                loadTransactions();
                loadBankNotes();
            } else {
                showLoginForm();
            }
            
            // Login form handler
            document.getElementById('login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const loginError = document.getElementById('login-error');
                
                loginError.style.display = 'none';
                
                SecureBank.login(username, password, function(response) {
                    showDashboard();
                    loadAccountInfo();
                    loadTransactions();
                    loadBankNotes();
                }, function(error) {
                    loginError.textContent = error;
                    loginError.style.display = 'block';
                });
            });
            
            // Register form handler
            document.getElementById('register-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const userData = {
                    username: document.getElementById('reg-username').value,
                    email: document.getElementById('reg-email').value,
                    password: document.getElementById('reg-password').value,
                    profile: {
                        fullName: document.getElementById('reg-fullname').value,
                        phone: document.getElementById('reg-phone').value
                    }
                };
                
                const registerError = document.getElementById('register-error');
                registerError.style.display = 'none';
                
                SecureBank.register(userData, function(response) {
                    alert('Registration successful! Please log in.');
                    showLoginForm();
                }, function(error) {
                    registerError.textContent = error;
                    registerError.style.display = 'block';
                });
            });
            
            // Logout button handler
            document.getElementById('logout-button').addEventListener('click', function() {
                SecureBank.logout();
                showLoginForm();
            });
            
            // Withdraw form handler
            document.getElementById('withdraw-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const amount = parseFloat(document.getElementById('withdraw-amount').value);
                const expiryDays = parseInt(document.getElementById('withdraw-expiry').value);
                const withdrawError = document.getElementById('withdraw-error');
                
                withdrawError.style.display = 'none';
                
                if (isNaN(amount) || amount <= 0) {
                    withdrawError.textContent = 'Please enter a valid amount';
                    withdrawError.style.display = 'block';
                    return;
                }
                
                // Generate a unique note ID and serial number
                const noteId = 'note_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const serialNumber = 'SB' + Date.now().toString().substr(-10) + Math.random().toString(36).substr(2, 6).toUpperCase();
                
                // Create a new bank note via API
                fetch(`${apiBaseUrl}/bank-notes/create.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: JSON.stringify({
                        note_id: noteId,
                        serial_number: serialNumber,
                        amount: amount,
                        expiry_days: expiryDays
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Display the new bank note
                        displayBankNote(data);
                        
                        // Refresh account info to show updated balance
                        loadAccountInfo();
                        
                        // Refresh bank notes list
                        loadBankNotes();
                    } else {
                        withdrawError.textContent = data.message;
                        withdrawError.style.display = 'block';
                    }
                })
                .catch(error => {
                    withdrawError.textContent = 'Error creating bank note: ' + error.message;
                    withdrawError.style.display = 'block';
                });
            });
            
            // Deposit form handler
            document.getElementById('deposit-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const noteIdentifier = document.getElementById('note-id').value.trim();
                const depositError = document.getElementById('deposit-error');
                const depositSuccess = document.getElementById('deposit-success');
                
                depositError.style.display = 'none';
                depositSuccess.style.display = 'none';
                
                if (!noteIdentifier) {
                    depositError.textContent = 'Please enter a bank note ID or serial number';
                    depositError.style.display = 'block';
                    return;
                }
                
                // Deposit the bank note via API
                fetch(`${apiBaseUrl}/bank-notes/deposit.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: JSON.stringify({
                        note_identifier: noteIdentifier
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        depositSuccess.textContent = `Successfully deposited bank note worth $${data.bank_note.amount.toFixed(2)}`;
                        depositSuccess.style.display = 'block';
                        document.getElementById('note-id').value = '';
                        
                        // Refresh account info to show updated balance
                        loadAccountInfo();
                        
                        // Refresh bank notes lists
                        loadBankNotes();
                    } else {
                        depositError.textContent = data.message;
                        depositError.style.display = 'block';
                    }
                })
                .catch(error => {
                    depositError.textContent = 'Error depositing bank note: ' + error.message;
                    depositError.style.display = 'block';
                });
            });
            
            // Upload note button handler
            document.getElementById('upload-note-btn').addEventListener('click', function() {
                showImportNoteModal('upload');
            });
            
            // Paste note button handler
            document.getElementById('paste-note-btn').addEventListener('click', function() {
                showImportNoteModal('paste');
            });
            
            // Import pasted note handler
            document.getElementById('import-pasted-note').addEventListener('click', function() {
                const noteJson = document.getElementById('note-json').value.trim();
                const pasteNoteError = document.getElementById('paste-note-error');
                
                pasteNoteError.style.display = 'none';
                
                if (!noteJson) {
                    pasteNoteError.textContent = 'Please paste the bank note JSON';
                    pasteNoteError.style.display = 'block';
                    return;
                }
                
                // Import the bank note via API
                fetch(`${apiBaseUrl}/bank-notes/import.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: JSON.stringify({
                        note_json: noteJson
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeImportNoteModal();
                        
                        // Ask user if they want to deposit the note now
                        if (data.action === 'deposit' && confirm(`Successfully imported bank note worth $${data.bank_note.amount.toFixed(2)}. Do you want to deposit it now?`)) {
                            depositBankNote(data.bank_note.note_id);
                        } else {
                            // Just refresh the bank notes list
                            loadBankNotes();
                        }
                    } else {
                        pasteNoteError.textContent = data.message;
                        pasteNoteError.style.display = 'block';
                    }
                })
                .catch(error => {
                    pasteNoteError.textContent = 'Error importing bank note: ' + error.message;
                    pasteNoteError.style.display = 'block';
                });
            });
            
            // Import uploaded note handler
            document.getElementById('import-uploaded-note').addEventListener('click', function() {
                const noteFile = document.getElementById('note-file').files[0];
                const uploadNoteError = document.getElementById('upload-note-error');
                
                uploadNoteError.style.display = 'none';
                
                if (!noteFile) {
                    uploadNoteError.textContent = 'Please select a bank note file';
                    uploadNoteError.style.display = 'block';
                    return;
                }
                
                const formData = new FormData();
                formData.append('note_file', noteFile);
                
                // Import the bank note via API
                fetch(`${apiBaseUrl}/bank-notes/import.php`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeImportNoteModal();
                        
                        // Ask user if they want to deposit the note now
                        if (data.action === 'deposit' && confirm(`Successfully imported bank note worth $${data.bank_note.amount.toFixed(2)}. Do you want to deposit it now?`)) {
                            depositBankNote(data.bank_note.note_id);
                        } else {
                            // Just refresh the bank notes list
                            loadBankNotes();
                        }
                    } else {
                        uploadNoteError.textContent = data.message;
                        uploadNoteError.style.display = 'block';
                    }
                })
                .catch(error => {
                    uploadNoteError.textContent = 'Error importing bank note: ' + error.message;
                    uploadNoteError.style.display = 'block';
                });
            });
            
            // Refresh created notes button handler
            document.getElementById('refresh-created-notes').addEventListener('click', function() {
                loadCreatedNotes(document.getElementById('status-filter-created').value);
            });
            
            // Refresh received notes button handler
            document.getElementById('refresh-received-notes').addEventListener('click', function() {
                loadReceivedNotes(document.getElementById('status-filter-received').value);
            });
            
            // Tab switching
            document.querySelectorAll('.tabs .tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    
                    // Update active tab
                    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding content
                    document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
                    document.getElementById(tabId + '-content').style.display = 'block';
                });
            });
            
            // Status filter handlers
            document.getElementById('status-filter-created').addEventListener('change', function() {
                loadCreatedNotes(this.value);
            });
            
            document.getElementById('status-filter-received').addEventListener('change', function() {
                loadReceivedNotes(this.value);
            });
            
            // Close modals when clicking on X or outside
            document.querySelectorAll('.close-modal').forEach(closeBtn => {
                closeBtn.addEventListener('click', function() {
                    document.querySelectorAll('.modal').forEach(modal => {
                        modal.style.display = 'none';
                    });
                });
            });
            
            window.addEventListener('click', function(event) {
                document.querySelectorAll('.modal').forEach(modal => {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
            
            // Close receipt button handler
            document.getElementById('close-receipt').addEventListener('click', function() {
                document.getElementById('receipt-modal').style.display = 'none';
            });
            
            // Print receipt button handler
            document.getElementById('print-receipt').addEventListener('click', function() {
                const receiptContent = document.getElementById('receipt-container').innerHTML;
                const printWindow = window.open('', '_blank');
                
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Bank Note Receipt</title>
                        <style>
                            body { font-family: 'Courier New', monospace; }
                            .receipt-header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; }
                            .receipt-row { display: flex; padding: 5px 0; border-bottom: 1px dashed #ddd; }
                            .receipt-label { flex: 1; font-weight: bold; }
                            .receipt-value { flex: 2; }
                            .receipt-footer { text-align: center; margin-top: 30px; border-top: 1px solid #000; padding-top: 10px; }
                        </style>
                    </head>
                    <body>
                        ${receiptContent}
                    </body>
                    </html>
                `);
                
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => printWindow.print(), 250);
            });
            
            // Download receipt button handler
            document.getElementById('download-receipt').addEventListener('click', function() {
                const noteId = document.getElementById('receipt-note-id').textContent;
                if (noteId) {
                    window.open(`${apiBaseUrl}/bank-notes/download-receipt.php?id=${encodeURIComponent(noteId)}`, '_blank');
                }
            });
            
            // New note button handler
            document.getElementById('new-note').addEventListener('click', function() {
                document.getElementById('bank-note-container').style.display = 'none';
                document.getElementById('withdraw-form').reset();
            });
            
            // Copy note button handler
            document.getElementById('copy-note').addEventListener('click', function() {
                const serialNumber = document.getElementById('banknote-serial').textContent;
                
                // Copy to clipboard
                navigator.clipboard.writeText(serialNumber).then(function() {
                    alert('Bank note serial number copied to clipboard');
                }, function() {
                    alert('Failed to copy. Please copy it manually: ' + serialNumber);
                });
            });
            
            // Print note button handler
            document.getElementById('print-note').addEventListener('click', function() {
                const bankNote = document.querySelector('.bank-note').cloneNode(true);
                const printWindow = window.open('', '_blank');
                
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Digital Bank Note</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            .bank-note { background: linear-gradient(to right, #f1f9ff, #e8f4fc); border: 1px solid #c0d6e4; 
                                       border-radius: 8px; padding: 15px; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
                            .bank-note-header { text-align: center; margin-bottom: 10px; font-weight: bold; color: #1a5276; 
                                             border-bottom: 1px solid #c0d6e4; padding-bottom: 5px; }
                            .bank-note-body { margin-bottom: 10px; }
                            .bank-note-footer { font-size: 0.8rem; color: #666; text-align: center; }
                            .bank-note-serial { position: absolute; top: 5px; right: 10px; font-size: 0.8rem; color: #1a5276; }
                            .bank-note-watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                                                font-size: 3rem; color: rgba(41, 128, 185, 0.1); font-weight: bold; }
                            .status-badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
                            .status-active { background-color: #27ae60; color: white; }
                        </style>
                    </head>
                    <body>
                        ${bankNote.outerHTML}
                    </body>
                    </html>
                `);
                
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => printWindow.print(), 250);
            });
            
            // Helper functions
            function showLoginForm() {
                document.getElementById('login-container').style.display = 'block';
                document.getElementById('register-container').style.display = 'none';
                document.getElementById('dashboard-container').style.display = 'none';
                document.getElementById('header-auth-status').innerHTML = '';
            }
            
            function showRegisterForm() {
                document.getElementById('login-container').style.display = 'none';
                document.getElementById('register-container').style.display = 'block';
                document.getElementById('dashboard-container').style.display = 'none';
                document.getElementById('header-auth-status').innerHTML = '';
            }
            
            function showDashboard() {
                document.getElementById('login-container').style.display = 'none';
                document.getElementById('register-container').style.display = 'none';
                document.getElementById('dashboard-container').style.display = 'block';
                document.getElementById('header-auth-status').innerHTML = 'Logged In';
            }
            
            function showImportNoteModal(activeTab = 'paste') {
                const modal = document.getElementById('import-note-modal');
                modal.style.display = 'block';
                
                // Reset form fields and errors
                document.getElementById('note-json').value = '';
                document.getElementById('note-file').value = '';
                document.getElementById('paste-note-error').style.display = 'none';
                document.getElementById('upload-note-error').style.display = 'none';
                
                // Set active tab
                document.querySelectorAll('#import-note-modal .tab').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.querySelector(`#import-note-modal .tab[data-tab="${activeTab}-note"]`).classList.add('active');
                
                // Show corresponding content
                document.getElementById('paste-note-content').style.display = activeTab === 'paste' ? 'block' : 'none';
                document.getElementById('upload-note-content').style.display = activeTab === 'upload' ? 'block' : 'none';
            }
            
            function closeImportNoteModal() {
                document.getElementById('import-note-modal').style.display = 'none';
            }
            
            function loadAccountInfo() {
                // Get account information via API
                fetch(`${apiBaseUrl}/account.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('account-name').textContent = data.account.profile.fullName || data.account.username;
                        document.getElementById('account-balance').textContent = '$' + data.account.balance.toFixed(2);
                    }
                })
                .catch(error => {
                    console.error('Error loading account info:', error);
                });
            }
            
            function loadTransactions() {
                // Get transactions via API
                fetch(`${apiBaseUrl}/transactions.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTransactions(data.transactions);
                    }
                })
                .catch(error => {
                    console.error('Error loading transactions:', error);
                });
            }
            
            function displayTransactions(transactions) {
                const transactionsList = document.getElementById('transactions-list');
                
                if (transactions.length === 0) {
                    transactionsList.innerHTML = '<div class="empty-state"><p>No transactions yet.</p></div>';
                    return;
                }
                
                let html = '';
                transactions.forEach(transaction => {
                    const isPositive = transaction.amount >= 0;
                    html += `
                        <div class="transaction-item">
                            <div>
                                <div>${transaction.description || transaction.type}</div>
                                <div class="transaction-date">${formatDate(transaction.timestamp)}</div>
                            </div>
                            <div class="transaction-amount ${isPositive ? 'amount-positive' : 'amount-negative'}">
                                ${isPositive ? '+' : ''}$${transaction.amount.toFixed(2)}
                            </div>
                        </div>
                    `;
                });
                
                transactionsList.innerHTML = html;
            }
            
            function loadBankNotes() {
                loadCreatedNotes(document.getElementById('status-filter-created').value);
                loadReceivedNotes(document.getElementById('status-filter-received').value);
            }
            
            function loadCreatedNotes(status = 'all') {
                // Get created notes via API
                fetch(`${apiBaseUrl}/bank-notes/created.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayNotes(data.bank_notes, 'created-notes-list');
                    }
                })
                .catch(error => {
                    console.error('Error loading created notes:', error);
                });
            }
            
            function loadReceivedNotes(status = 'all') {
                // Get received notes via API
                fetch(`${apiBaseUrl}/bank-notes/received.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayNotes(data.bank_notes, 'received-notes-list');
                    }
                })
                .catch(error => {
                    console.error('Error loading received notes:', error);
                });
            }
            
            function displayNotes(notes, containerId) {
                const container = document.getElementById(containerId);
                
                if (notes.length === 0) {
                    container.innerHTML = '<div class="empty-state"><p>No bank notes found.</p></div>';
                    return;
                }
                
                // Clone template and fill in data for each note
                let html = '';
                notes.forEach(note => {
                    const template = document.getElementById('note-item-template').content.cloneNode(true);
                    
                    template.querySelector('.note-serial').textContent = note.serial;
                    
                    const statusBadge = document.createElement('span');
                    statusBadge.className = `status-badge status-${note.status.toLowerCase()}`;
                    statusBadge.textContent = note.status.toUpperCase();
                    template.querySelector('.note-status').appendChild(statusBadge);
                    
                    template.querySelector('.note-amount').textContent = '$' + note.amount.toFixed(2);
                    template.querySelector('.note-issued').textContent = formatDate(note.issued_at);
                    template.querySelector('.note-expires').textContent = formatDate(note.expires_at);
                    template.querySelector('.note-issuer').textContent = note.issuer;
                    
                    // View receipt button
                    template.querySelector('.btn-view').addEventListener('click', function() {
                        viewReceipt(note.note_id);
                    });
                    
                    // Download button (only for active notes)
                    const downloadBtn = template.querySelector('.btn-download');
                    if (note.status !== 'active') {
                        downloadBtn.style.display = 'none';
                    } else {
                        downloadBtn.addEventListener('click', function() {
                            downloadBankNote(note);
                        });
                    }
                    
                    html += template.querySelector('.note-item').outerHTML;
                });
                
                container.innerHTML = html;
            }
            
            function displayBankNote(noteData) {
                const container = document.getElementById('bank-note-container');
                
                // Fill in the bank note details
                document.getElementById('banknote-serial').textContent = noteData.serial;
                document.getElementById('banknote-amount').textContent = '$' + noteData.amount.toFixed(2);
                document.getElementById('banknote-issuer').textContent = noteData.issuer;
                document.getElementById('banknote-issued').textContent = formatDate(noteData.issued_at);
                document.getElementById('banknote-expires').textContent = formatDate(noteData.expires_at);
                
                // Show the bank note container
                container.style.display = 'block';
            }
            
            function depositBankNote(noteId) {
                // Deposit the bank note via API
                fetch(`${apiBaseUrl}/bank-notes/deposit.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: JSON.stringify({
                        note_identifier: noteId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Successfully deposited bank note worth $${data.bank_note.amount.toFixed(2)}`);
                        
                        // Refresh account info to show updated balance
                        loadAccountInfo();
                        
                        // Refresh bank notes lists
                        loadBankNotes();
                    } else {
                        alert('Error depositing bank note: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error depositing bank note: ' + error.message);
                });
            }
            
            function viewReceipt(noteId) {
                // Get receipt via API
                fetch(`${apiBaseUrl}/bank-notes/receipt.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getSessionToken()
                    },
                    body: JSON.stringify({
                        note_id: noteId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayReceipt(data.receipt);
                    } else {
                        alert('Error getting receipt: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error getting receipt: ' + error.message);
                });
            }
            
            function displayReceipt(receipt) {
                // Clone receipt template
                const receiptTemplate = document.getElementById('bank-note-receipt-template').content.cloneNode(true);
                
                // Fill in receipt details
                receiptTemplate.getElementById('receipt-transaction-id').textContent = receipt.transaction_id || 'N/A';
                receiptTemplate.getElementById('receipt-date').textContent = formatDate(receipt.date);
                receiptTemplate.getElementById('receipt-note-id').textContent = receipt.note_id;
                receiptTemplate.getElementById('receipt-serial').textContent = receipt.serial;
                receiptTemplate.getElementById('receipt-amount').textContent = '$' + receipt.amount.toFixed(2);
                receiptTemplate.getElementById('receipt-issuer').textContent = receipt.issuer;
                receiptTemplate.getElementById('receipt-expiry').textContent = formatDate(receipt.expiry);
                
                // Add redemption info if available
                if (receipt.redeemed_at) {
                    receiptTemplate.getElementById('receipt-redemption-row').style.display = 'flex';
                    receiptTemplate.getElementById('receipt-redemption').textContent = formatDate(receipt.redeemed_at);
                }
                
                // Display in modal
                const receiptContainer = document.getElementById('receipt-container');
                receiptContainer.innerHTML = '';
                receiptContainer.appendChild(receiptTemplate);
                
                document.getElementById('receipt-modal').style.display = 'block';
            }
            
            function downloadBankNote(note) {
                // Create bank note JSON
                const template = document.getElementById('bank-note-file-template').innerHTML;
                const noteJson = template
                    .replace('{{note_id}}', note.note_id)
                    .replace('{{serial}}', note.serial)
                    .replace('{{amount}}', note.amount)
                    .replace('{{issuer}}', note.issuer)
                    .replace('{{issuer_id}}', note.issuer_id)
                    .replace('{{issued_at}}', note.issued_at)
                    .replace('{{expires_at}}', note.expires_at)
                    .replace('{{signature}}', note.signature);
                
                // Create and download file
                const blob = new Blob([noteJson], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `bank_note_${note.serial}.sbnt`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
            
            function getSessionToken() {
                // Get the session token from localStorage
                const sessionData = localStorage.getItem('secure_bank_session');
                if (sessionData) {
                    try {
                        const session = JSON.parse(sessionData);
                        if (session.id && session.expiry > Date.now() / 1000) {
                            return session.id;
                        }
                    } catch (e) {
                        console.error('Error parsing session data:', e);
                    }
                }
                return null;
            }
            
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleString();
            }
            
            // Toggle between login and register forms
            document.getElementById('show-register').addEventListener('click', function(e) {
                e.preventDefault();
                showRegisterForm();
            });
            
            document.getElementById('show-login').addEventListener('click', function(e) {
                e.preventDefault();
                showLoginForm();
            });
            
            // Setup drag and drop for bank note import
            const dragDropArea = document.querySelector('.drag-drop-area');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dragDropArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dragDropArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dragDropArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dragDropArea.classList.add('dragover');
            }
            
            function unhighlight() {
                dragDropArea.classList.remove('dragover');
            }
            
            dragDropArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length > 0) {
                    document.getElementById('note-file').files = files;
                }
            }
        });
    </script>
</body>
</html>