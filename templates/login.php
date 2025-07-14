<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HeartGuard AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --background-color: #f8f9fc;
            --dark-color: #2e384d;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color), #2e59d9);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 900px;
            min-height: 400px;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        .login-sidebar {
            width: 260px;
            background: linear-gradient(135deg, var(--primary-color), #2e59d9);
            padding: 2rem;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-main {
            flex: 1;
            padding: 2rem;
            max-height: 600px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) transparent;
        }

        .login-main::-webkit-scrollbar {
            width: 6px;
        }

        .login-main::-webkit-scrollbar-track {
            background: transparent;
        }

        .login-main::-webkit-scrollbar-thumb {
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        .sidebar-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0.5rem 0;
            transition: all 0.3s ease;
        }

        .sidebar-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .sidebar-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        .login-header {
            text-align: left;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--secondary-color);
            font-size: 1rem;
        }

        .form-control, .btn {
            border-radius: 8px;
        }

        .metamask-btn {
            border-radius: 8px;
            padding: 0.8rem;
            background: linear-gradient(135deg, #F6851B, #E4761B);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 1.5rem;
            width: 100%;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .metamask-btn.connected {
            background: linear-gradient(135deg, var(--success-color), #15a173);
        }

        .metamask-btn:hover {
            transform: translateY(-2px);
        }

        /* Update existing styles for smaller elements */
        .input-group {
            margin-bottom: 1rem;
        }

        .form-control {
            padding: 0.6rem 1rem;
        }

        .btn-primary {
            padding: 0.7rem;
        }

        .login-image {
            flex: 1;
            background: linear-gradient(135deg, #4e73df80, #2e59d980),
                        url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: white;
            text-align: center;
        }

        .login-image-content {
            position: relative;
            z-index: 1;
        }

        .login-image h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .login-image p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 400px;
            margin: 0 auto;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .wallet-address {
            background: rgba(78, 115, 223, 0.1);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            word-break: break-all;
            font-size: 0.9rem;
            color: var(--dark-color);
            border: 1px solid rgba(78, 115, 223, 0.2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .wallet-address i {
            color: var(--success-color);
        }

        .alert {
            border: none;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .text-center a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .text-center a:hover {
            color: #2e59d9;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
            }

            .login-sidebar {
                width: 100%;
                padding: 1.5rem;
                flex-direction: row;
                justify-content: space-around;
            }

            .login-main {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-sidebar">
            <div class="sidebar-icon">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="sidebar-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="sidebar-icon">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="sidebar-icon">
                <i class="fas fa-notes-medical"></i>
            </div>
        </div>
        <div class="login-main">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Please login to your account</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <button id="connectMetamask" class="metamask-btn" tabindex="1" role="button" aria-label="Connect MetaMask wallet">
                <i class="fab fa-ethereum" aria-hidden="true"></i>
                Connect MetaMask
            </button>

            <div id="walletAddress" class="wallet-address d-none" aria-live="polite">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                <span>Connected: <span id="address"></span></span>
            </div>

            <form method="POST" action="index.php?route=login" id="loginForm">
                <input type="hidden" name="wallet_address" id="wallet_address">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="Enter your username" tabindex="2">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password" tabindex="3">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="4" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100" tabindex="5">
                    <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Login
                </button>
            </form>

            <div class="text-center mt-3">
                <p>Don't have an account? <a href="index.php?route=signup" tabindex="6">Sign Up</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/web3@1.5.2/dist/web3.min.js"></script>
    <script>
        let web3;
        const connectMetamask = document.getElementById('connectMetamask');
        const walletAddress = document.getElementById('walletAddress');
        const addressSpan = document.getElementById('address');
        const walletAddressInput = document.getElementById('wallet_address');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const loginForm = document.getElementById('loginForm');

        // Toggle password visibility
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? 
                '<i class="fas fa-eye"></i>' : 
                '<i class="fas fa-eye-slash"></i>';
        });

        // Form validation
        loginForm.addEventListener('submit', function(e) {
            if (!walletAddressInput.value) {
                e.preventDefault();
                alert('Please connect your MetaMask wallet first.');
                return false;
            }
            
            // Log the wallet address for debugging
            console.log('Submitting form with wallet address:', walletAddressInput.value);
        });

        // Update MetaMask button style when connected
        function updateMetaMaskButton(connected) {
            const button = document.getElementById('connectMetamask');
            if (connected) {
                button.classList.add('connected');
                button.innerHTML = '<i class="fas fa-check me-2"></i>Connected';
            } else {
                button.classList.remove('connected');
                button.innerHTML = '<i class="fab fa-ethereum"></i>Connect MetaMask';
            }
        }

        // Connect wallet function
        async function connectWallet() {
            if (typeof window.ethereum !== 'undefined') {
                try {
                    const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
                    const account = accounts[0];
                    addressSpan.textContent = account;
                    walletAddressInput.value = account;
                    walletAddress.classList.remove('d-none');
                    updateMetaMaskButton(true);
                } catch (error) {
                    console.error('Error connecting to MetaMask:', error);
                    alert('Failed to connect to MetaMask. Please try again.');
                }
            } else {
                alert('MetaMask is not installed. Please install it to continue.');
                window.open('https://metamask.io/download.html', '_blank');
            }
        }

        connectMetamask.addEventListener('click', connectWallet);

        // Check initial connection
        window.addEventListener('load', async () => {
            if (typeof window.ethereum !== 'undefined') {
                try {
                    const accounts = await window.ethereum.request({ method: 'eth_accounts' });
                    if (accounts.length > 0) {
                        const account = accounts[0];
                        addressSpan.textContent = account;
                        walletAddressInput.value = account;
                        walletAddress.classList.remove('d-none');
                        updateMetaMaskButton(true);
                    }
                } catch (error) {
                    console.error('Error checking MetaMask connection:', error);
                }
            }
        });
    </script>
</body>
</html> 