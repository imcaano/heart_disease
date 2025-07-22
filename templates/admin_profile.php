<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            overflow-x: hidden;
        }

        #sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            transition: var(--transition);
            z-index: 1000;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        #content {
            margin-left: 250px;
            padding: 20px;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 15px 20px;
            transition: var(--transition);
            border-radius: 5px;
            margin: 5px 10px;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff !important;
            transform: translateX(5px);
        }

        .nav-link i {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            margin-right: 10px;
            transition: var(--transition);
        }

        .nav-link:hover i {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .profile-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
            animation: fadeIn 0.5s ease-out forwards;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-section {
            padding: 20px;
            color: #fff;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }

        .profile-section .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            margin-right: 10px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 2rem;
            font-size: 3rem;
            color: white;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid rgba(0,0,0,0.05);
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78,115,223,0.1);
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            border: none;
            padding: 0.75rem 2rem;
            color: white;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78,115,223,0.2);
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: var(--transition);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .activity-item {
            padding: 1rem;
            border-left: 3px solid var(--primary-color);
            margin-bottom: 1rem;
            background: rgba(78,115,223,0.05);
            border-radius: 0 10px 10px 0;
            transition: var(--transition);
        }

        .activity-item:hover {
            transform: translateX(5px);
            background: rgba(78,115,223,0.1);
        }

        /* Timeline styling */
        .timeline {
            position: relative;
            padding: 0;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .activity-content {
            background-color: #fff;
            border-radius: 0.5rem;
            padding: 0.75rem;
            flex-grow: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .timeline-connector {
            position: relative;
            height: 30px;
            margin-left: 18px;
            margin-bottom: 5px;
        }
        
        .timeline-connector:before {
            content: '';
            position: absolute;
            width: 2px;
            height: 100%;
            background-color: rgba(78,115,223,0.2);
            left: 0;
            top: 0;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="text-white mb-0">Heart Disease</h4>
            <small class="text-white-50">Prediction System</small>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_dashboard">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_predict">
                    <i class="fas fa-heartbeat"></i>
                    Predict
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_appointments">
                    <i class="fas fa-calendar-check"></i>
                    Appointments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=manage_users">
                    <i class="fas fa-users"></i>
                 Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_reports">
                    <i class="fas fa-file-alt"></i>
                    Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_profile">
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
                <div>
                    <h6 class="mb-0 text-white"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></h6>
                    <small class="text-white-50"><?php echo isset($_SESSION['user']['role']) ? htmlspecialchars($_SESSION['user']['role']) : 'Administrator'; ?></small>
                </div>
            </div>
            <a href="index.php?route=logout" class="btn btn-danger mt-3 w-100">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="profile-card mt-4 mx-auto" style="max-width: 600px;">
                        <div id="passwordSuccessAlert" style="display:none;" class="alert alert-success text-center mb-4">Password changed successfully!</div>
                        <div class="profile-header">
                            <div class="profile-avatar" style="font-size:2.5rem; background: #4e73df; color: #fff; border-radius: 16px; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin-right: 2rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h2 class="mb-1" style="color: #2e59d9; font-weight: 700;"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></h2>
                                <p class="text-muted mb-0"><i class="fas fa-shield-alt me-2"></i><?php echo isset($_SESSION['user']['role']) ? htmlspecialchars($_SESSION['user']['role']) : 'Administrator'; ?></p>
                            </div>
                        </div>
                        <form id="profileForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo isset($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Wallet Address</label>
                                    <input type="text" class="form-control" id="wallet_address" name="wallet_address" value="<?php echo isset($_SESSION['user']['wallet_address']) ? htmlspecialchars($_SESSION['user']['wallet_address']) : ''; ?>" readonly>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-save"><i class="fas fa-save me-2"></i>Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('profileForm').onsubmit = async function(e) {
        e.preventDefault();
        // ... your AJAX logic ...
        // On success:
        document.getElementById('passwordSuccessAlert').style.display = 'block';
        setTimeout(() => { document.getElementById('passwordSuccessAlert').style.display = 'none'; }, 4000);
    };
    </script>
</body>
</html> 