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
    <title>Sign Up</title>
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
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 900px;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        .signup-sidebar {
            width: 260px;
            background: linear-gradient(135deg, var(--primary-color), #2e59d9);
            padding: 2rem;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .signup-main {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }

        .form-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .input-group {
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.5rem 1rem;
            font-size: 0.95rem;
        }

        .metamask-btn {
            padding: 0.7rem;
            margin-bottom: 1rem;
        }

        .wallet-address {
            padding: 0.7rem;
            margin-bottom: 1rem;
        }

        .password-strength {
            margin-top: 4px;
            margin-bottom: 4px;
        }

        .password-strength-text {
            margin-top: 2px;
            margin-bottom: 4px;
        }

        .signup-header {
            margin-bottom: 1.5rem;
        }

        .signup-header h2 {
            margin-bottom: 0.3rem;
        }

        .btn-primary {
            margin-top: 0.5rem;
        }

        .text-center {
            margin-top: 1rem;
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

        .alert {
            border: none;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            padding: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
        }

        .strength-weak { 
            width: 33.33%; 
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        .strength-medium { 
            width: 66.66%; 
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }
        .strength-strong { 
            width: 100%; 
            background: linear-gradient(135deg, #28a745, #218838);
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .signup-container {
                flex-direction: column;
                max-width: 400px;
            }

            .signup-sidebar {
                width: 100%;
                padding: 1rem;
                flex-direction: row;
                justify-content: space-around;
            }

            .signup-main {
                padding: 1.5rem;
            }

            .form-container {
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-sidebar">
            <div class="sidebar-icons-top">
                <div class="sidebar-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="sidebar-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            <div class="sidebar-icons-bottom">
                <div class="sidebar-icon">
                    <i class="fas fa-key"></i>
                </div>
                <div class="sidebar-icon">
                    <i class="fas fa-heart"></i>
                </div>
            </div>
        </div>
        <div class="signup-main">
            <div class="signup-header">
                <h2>Create Account</h2>
                <p>Please fill in your information</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <button id="connectMetamask" class="metamask-btn">
                    <i class="fab fa-ethereum"></i>
                    Connect MetaMask
                </button>

                <div id="walletAddress" class="wallet-address d-none">
                    <i class="fas fa-check-circle"></i>
                    <span>Connected: <span id="address"></span></span>
                </div>

                <form method="POST" action="index.php?route=signup" id="signupForm">
                    <input type="hidden" name="wallet_address" id="wallet_address">
                    <div class="mb-2">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required placeholder="Choose a username">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Create a password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar"></div>
                        </div>
                        <div class="password-strength-text">Password strength: <span id="strengthText">None</span></div>
                    </div>
                    <div class="mb-2">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                </form>

                <div class="text-center">
                    <p>Already have an account? <a href="index.php?route=login">Login</a></p>
                </div>
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
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const signupForm = document.getElementById('signupForm');
        const strengthBar = document.querySelector('.password-strength-bar');
        const strengthText = document.getElementById('strengthText');

        // Toggle password visibility
        function togglePasswordVisibility(inputField, button) {
            const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
            inputField.setAttribute('type', type);
            button.innerHTML = type === 'password' ? 
                '<i class="fas fa-eye"></i>' : 
                '<i class="fas fa-eye-slash"></i>';
        }

        togglePassword.addEventListener('click', () => togglePasswordVisibility(passwordInput, togglePassword));
        toggleConfirmPassword.addEventListener('click', () => togglePasswordVisibility(confirmPasswordInput, toggleConfirmPassword));

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;

            switch (strength) {
                case 0:
                    strengthBar.className = 'password-strength-bar';
                    strengthText.textContent = 'None';
                    break;
                case 1:
                case 2:
                    strengthBar.className = 'password-strength-bar strength-weak';
                    strengthText.textContent = 'Weak';
                    break;
                case 3:
                    strengthBar.className = 'password-strength-bar strength-medium';
                    strengthText.textContent = 'Medium';
                    break;
                case 4:
                    strengthBar.className = 'password-strength-bar strength-strong';
                    strengthText.textContent = 'Strong';
                    break;
            }
        }

        passwordInput.addEventListener('input', () => checkPasswordStrength(passwordInput.value));

        // Form validation
        signupForm.addEventListener('submit', function(e) {
            if (!walletAddressInput.value) {
                e.preventDefault();
                alert('Please connect your MetaMask wallet first.');
                return false;
            }

            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                alert('Passwords do not match.');
                return false;
            }
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