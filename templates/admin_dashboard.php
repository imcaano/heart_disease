<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Heart Disease Prediction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --secondary-color: #858796;
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

        #sidebar.collapsed {
            left: -250px;
        }

        #content {
            margin-left: 250px;
            transition: var(--transition);
            padding: 20px;
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

        .stat-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            text-align: center;
        }

        .stat-card.users {
            border-left: 4px solid var(--primary-color);
        }

        .stat-card.predictions {
            border-left: 4px solid var(--success-color);
        }

        .stat-card.positive {
            border-left: 4px solid #ef4444;
        }

        .stat-card.negative {
            border-left: 4px solid #22c55e;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
            transition: var(--transition);
            color: white;
        }

        .stat-icon.users {
            background: var(--primary-color);
        }

        .stat-icon.predictions {
            background: var(--success-color);
        }

        .stat-icon.positive {
            background: #ef4444;
        }

        .stat-icon.negative {
            background: #22c55e;
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

        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            margin-top: 10px;
            width: 100%;
            transition: var(--transition);
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
    </style>
</head>
<body>
    <button class="toggle-sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="text-white mb-0">Heart Disease</h4>
            <small class="text-white-50">Prediction System</small>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link active" href="index.php?route=admin_dashboard">
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
                    <h6 class="mb-0 text-white"><?php echo isset($_SESSION['user']['username']) ? htmlspecialchars($_SESSION['user']['username']) : 'Admin'; ?></h6>
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
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card users">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="mb-2" id="totalUsers">0</h3>
                        <p class="text-muted mb-0">Total Users</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card predictions">
                        <div class="stat-icon predictions">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3 class="mb-2" id="totalPredictions">0</h3>
                        <p class="text-muted mb-0">Total Predictions</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card positive">
                        <div class="stat-icon positive">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="mb-2" id="positivePredictions">0</h3>
                        <p class="text-muted mb-0">High Risk Predictions</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card negative">
                        <div class="stat-icon negative">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="mb-2" id="negativePredictions">0</h3>
                        <p class="text-muted mb-0">Low Risk Predictions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        $('.toggle-sidebar').click(function() {
            $('#sidebar').toggleClass('collapsed');
            $('#content').toggleClass('expanded');
        });

        // Function to fetch and update dashboard data
        async function updateDashboardData() {
            try {
                const response = await fetch('api/dashboard_stats.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
                // Update all stats
                if (typeof data.totalUsers === 'number') {
                    animateNumber('totalUsers', data.totalUsers);
                }
                if (typeof data.totalPredictions === 'number') {
                    animateNumber('totalPredictions', data.totalPredictions);
                }
                if (typeof data.positivePredictions === 'number') {
                    animateNumber('positivePredictions', data.positivePredictions);
                }
                if (typeof data.negativePredictions === 'number') {
                    animateNumber('negativePredictions', data.negativePredictions);
                }
            } catch (error) {
                console.error('Error:', error);
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger';
                errorMessage.textContent = 'Error loading data. Please refresh the page.';
                document.querySelector('.container-fluid').prepend(errorMessage);
            }
        }

        // Helper function to animate numbers
        function animateNumber(elementId, finalValue) {
            const element = document.getElementById(elementId);
            if (!element) {
                console.error('Element not found:', elementId);
                return;
            }

            // Clear any existing animation
            if (element._animationInterval) {
                clearInterval(element._animationInterval);
            }

            const startValue = parseInt(element.textContent) || 0;
            const duration = 1000;
            const steps = 60;
            const stepValue = (finalValue - startValue) / steps;
            let currentStep = 0;

            element._animationInterval = setInterval(() => {
                currentStep++;
                const currentValue = Math.floor(startValue + (stepValue * currentStep));
                element.textContent = currentValue;

                if (currentStep >= steps) {
                    element.textContent = finalValue;
                    clearInterval(element._animationInterval);
                    element._animationInterval = null;
                }
            }, duration / steps);
        }

        // Load data when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateDashboardData();
            // Refresh data every 30 seconds
            setInterval(updateDashboardData, 30000);
        });
    </script>
</body>
</html> 