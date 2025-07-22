<?php
require_once dirname(__DIR__) . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php?route=login');
    exit;
}

// Check if user has appointments or positive predictions
$user_id = $_SESSION['user']['id'];
$hasAppointments = false;
$hasPositivePredictions = false;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check for appointments
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $hasAppointments = $stmt->fetchColumn() > 0;
    
    // Check for positive predictions
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM predictions WHERE user_id = ? AND prediction = 1");
    $stmt->execute([$user_id]);
    $hasPositivePredictions = $stmt->fetchColumn() > 0;
    
} catch (PDOException $e) {
    // Silently handle database errors
    error_log("Dashboard database error: " . $e->getMessage());
}

$showAppointmentsLink = $hasAppointments || $hasPositivePredictions;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Heart Disease Prediction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #eaf6fb 0%, #f8f9fa 100%);
            min-height: 100vh;
        }
        .expert-glass {
            background: rgba(255,255,255,0.7);
            box-shadow: 0 8px 32px 0 rgba(46,89,217,0.10);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 20px;
            border: 1.5px solid rgba(46,89,217,0.10);
            max-width: 480px;
            margin: 0 auto;
            padding: 2.5rem 2rem 2rem 2rem;
            position: relative;
        }
        .expert-accent {
            width: 48px;
            height: 6px;
            border-radius: 3px;
            background: linear-gradient(90deg, #4e73df 0%, #36b9cc 100%);
            margin: 0 auto 1.5rem auto;
        }
        .expert-icon {
            font-size: 3.5rem;
            color: #36b9cc;
            margin-bottom: 1.2rem;
            filter: drop-shadow(0 2px 8px #36b9cc22);
        }
        .expert-welcome-title {
            color: #2e59d9;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        .expert-welcome-subtitle {
            color: #4e73df;
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 1.2rem;
        }
        .expert-cta-btn {
            font-weight: 700;
            border-radius: 10px;
            font-size: 1.1rem;
            padding: 0.75rem 2.5rem;
            background: linear-gradient(90deg, #4e73df 0%, #36b9cc 100%);
            border: none;
            color: #fff;
            box-shadow: 0 2px 8px #4e73df22;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .expert-cta-btn:hover {
            background: linear-gradient(90deg, #36b9cc 0%, #4e73df 100%);
            box-shadow: 0 4px 16px #36b9cc33;
        }
        .expert-tip {
            margin-top: 2rem;
            color: #169a6b;
            font-size: 1.05rem;
            font-style: italic;
            text-align: center;
        }
    </style>
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
                <a class="nav-link active" href="index.php?route=dashboard">
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
            <?php if ($showAppointmentsLink): ?>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=user_appointments">
                    <i class="fas fa-calendar-check"></i>
                    My Appointments
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=profile">
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
                            <h4 class="mb-0">Dashboard</h4>
                            <p class="text-muted mb-0">Welcome to your heart disease prediction dashboard</p>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center" style="min-height: 400px; background: none; border-radius: 0; box-shadow: none;">
                            <div class="expert-glass">
                                <div class="expert-accent"></div>
                                <div class="expert-icon">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                                <h1 class="expert-welcome-title">Welcome, <?php echo isset($_SESSION['user']['username']) ? htmlspecialchars($_SESSION['user']['username']) : 'User'; ?>!</h1>
                                <div class="expert-welcome-subtitle">Your trusted heart health expert portal</div>
                                <p class="lead mb-4" style="color: #444; font-size: 1.15rem;">Get personalized insights and guidance for a healthier future. Start a new prediction or explore your profile for more details.</p>
                                <a href="index.php?route=predict" class="expert-cta-btn">Start New Prediction <i class="fas fa-arrow-right ms-2"></i></a>
                                <?php if ($hasPositivePredictions && !$hasAppointments): ?>
                                <div class="mt-3">
                                    <a href="index.php?route=predict" class="btn btn-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Book Consultation (High Risk Detected)
                                    </a>
                                </div>
                                <?php endif; ?>
                                <div class="expert-tip mt-4">"Prevention is better than cure. Take the first step to a healthier heart today!"</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Remove all chart.js code and updateDashboardData to only update numbers and table
        async function updateDashboardData() {
            try {
                const response = await fetch('api/user_stats.php');
                const data = await response.json();
                if (!data.success) throw new Error(data.message || 'Failed to load dashboard data');
                document.getElementById('totalPredictions').textContent = data.totalPredictions;
                document.getElementById('highRiskCount').textContent = data.highRiskCount;
                document.getElementById('lowRiskCount').textContent = data.lowRiskCount;
                // Update recent predictions table
                const tbody = document.getElementById('recentPredictionsBody');
                tbody.innerHTML = '';
                if (data.recentPredictions && data.recentPredictions.length > 0) {
                    data.recentPredictions.forEach(pred => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${pred.created_at || ''}</td><td>${pred.result === 1 ? 'Positive' : 'Negative'}</td><td>${pred.confidence ? (Math.round(pred.confidence * 100) + '%') : '-'}</td>`;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center">No predictions found.</td></tr>';
                }
            } catch (error) {
                document.getElementById('recentPredictionsBody').innerHTML = `<tr><td colspan="3" class="text-danger">${error.message}</td></tr>`;
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            updateDashboardData();
            setInterval(updateDashboardData, 30000);
        });

        // Sidebar Toggle
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('content').classList.toggle('sidebar-active');
        });
    </script>
</body>
</html> 