<?php
// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php?route=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Heart Disease Prediction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button id="sidebarCollapse" class="btn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">Heart Disease</h4>
            <small>Prediction System</small>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=dashboard">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=predict">
                    <i class="fas fa-heartbeat"></i>
                    New Prediction
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="index.php?route=profile">
                    <i class="fas fa-user-circle"></i>
                    Profile
                </a>
            </li>
        </ul>

        <div class="profile-section mt-auto">
            <div class="d-flex align-items-center">
                <div class="profile-img">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h6 class="mb-0"><?php echo isset($_SESSION['user']['username']) ? htmlspecialchars($_SESSION['user']['username']) : 'User'; ?></h6>
                    <small><?php echo isset($_SESSION['user']['role']) ? htmlspecialchars($_SESSION['user']['role']) : 'User'; ?></small>
            </div>
            </div>
            <a href="index.php?route=logout" class="logout-btn mt-3">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Profile</h4>
                            <p class="text-muted mb-0">Manage your account settings</p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center mb-4">
                                        <div class="profile-avatar">
                                            <?php echo strtoupper(substr($_SESSION['user']['username'], 0, 1)); ?>
                                        </div>
                                        <h4 class="mt-3"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></h4>
                                        <p class="text-muted"><?php echo ucfirst($_SESSION['user']['role']); ?></p>
                                            </div>
                                            </div>
                                <div class="col-md-8">
                                    <div class="card profile-info-card mt-4 mx-auto" style="max-width: 600px;">
                                        <div id="passwordSuccessAlert" style="display:none;" class="alert alert-success text-center mb-4">Password changed successfully!</div>
                                        <div id="profileSuccessAlert" style="display:none;" class="alert alert-success text-center mb-4">Profile updated successfully!</div>
                                        <div id="profileErrorAlert" style="display:none;" class="alert alert-danger text-center mb-4"></div>
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <div class="profile-avatar mb-3" style="font-size:2.5rem; background: #4e73df; color: #fff; border-radius: 16px; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                    <?php echo strtoupper(substr($_SESSION['user']['username'], 0, 1)); ?>
                                                </div>
                                                <h4 class="mt-2 mb-0" style="color: #2e59d9; font-weight: 700;"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></h4>
                                                <p class="text-muted mb-2"><?php echo ucfirst($_SESSION['user']['role']); ?></p>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="profile-info-item mb-3">
                                                    <div class="profile-info-icon"><i class="fas fa-user"></i></div>
                                                    <div class="profile-info-content">
                                                        <div class="profile-info-label">Username</div>
                                                        <div class="profile-info-value"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
                                                    </div>
                                                </div>
                                                <div class="profile-info-item mb-3">
                                                    <div class="profile-info-icon"><i class="fas fa-envelope"></i></div>
                                                    <div class="profile-info-content">
                                                        <div class="profile-info-label">Email</div>
                                                        <div class="profile-info-value"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></div>
                                                    </div>
                                                </div>
                                                <div class="profile-info-item mb-3">
                                                    <div class="profile-info-icon"><i class="fas fa-wallet"></i></div>
                                                    <div class="profile-info-content">
                                                        <div class="profile-info-label">Wallet Address</div>
                                                        <div class="profile-info-value wallet" id="walletAddress"><?php echo htmlspecialchars($_SESSION['user']['wallet_address']); ?></div>
                                                        <button class="copy-btn mt-2" onclick="copyWalletAddress()"><i class="fas fa-copy"></i> Copy</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="password-form mt-3">
                                            <h5 class="mb-3">Update Profile Information</h5>
                                            <form id="profileForm">
                                                <div class="mb-3">
                                                    <label for="username" class="form-label">Username</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($_SESSION['user']['username']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-save me-2"></i>Update Profile
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <hr>
                                        <div class="password-form mt-3">
                                            <h5 class="mb-3">Update Password</h5>
                                            <form id="passwordForm">
                                                <div class="mb-3">
                                                    <label for="currentPassword" class="form-label">Current Password</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                        <input type="password" class="form-control" id="currentPassword" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="newPassword" class="form-label">New Password</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                        <input type="password" class="form-control" id="newPassword" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                                        <input type="password" class="form-control" id="confirmPassword" required>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-save me-2"></i>Update Password
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('content').classList.toggle('sidebar-active');
        });

        // Copy Wallet Address
        function copyWalletAddress() {
            const walletAddress = document.getElementById('walletAddress').textContent;
            navigator.clipboard.writeText(walletAddress).then(() => {
                const copyBtn = document.querySelector('.copy-btn');
                const originalText = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    copyBtn.innerHTML = originalText;
                }, 2000);
            });
        }

        // Profile Update Form
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            document.getElementById('profileSuccessAlert').style.display = 'none';
            document.getElementById('profileErrorAlert').style.display = 'none';
            
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('index.php?route=update_profile', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        username: username,
                        email: email
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('profileSuccessAlert').style.display = 'block';
                    setTimeout(() => {
                        document.getElementById('profileSuccessAlert').style.display = 'none';
                    }, 4000);
                    
                    // Update the displayed username and email on the page
                    document.querySelectorAll('.profile-info-value')[0].textContent = username;
                    document.querySelectorAll('.profile-info-value')[1].textContent = email;
                    
                    // Update the profile avatar initial
                    document.querySelectorAll('.profile-avatar').forEach(avatar => {
                        avatar.textContent = username.charAt(0).toUpperCase();
                    });
                } else {
                    document.getElementById('profileErrorAlert').textContent = result.message || 'Error updating profile.';
                    document.getElementById('profileErrorAlert').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('profileErrorAlert').textContent = 'An error occurred. Please try again.';
                document.getElementById('profileErrorAlert').style.display = 'block';
                console.error('Error:', error);
            } finally {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        });

        // Password Update Form
        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match!');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('index.php?route=update_password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('Password updated successfully!');
                    this.reset();
                    document.getElementById('passwordSuccessAlert').style.display = 'block';
                    setTimeout(() => { document.getElementById('passwordSuccessAlert').style.display = 'none'; }, 4000);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html> 