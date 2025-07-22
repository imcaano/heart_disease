<?php
// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php?route=login');
    exit;
}

// Get user's appointments from database
require_once 'config.php';
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$user_id = $_SESSION['user']['id'];

// Get appointments for this user
$query = "
    SELECT a.*, p.prediction as prediction_result
    FROM appointments a
    LEFT JOIN predictions p ON a.prediction_id = p.id
    WHERE a.user_id = :user_id
    ORDER BY a.created_at DESC
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Heart Disease Prediction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(120deg, #eaf6fb 0%, #f8f9fa 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .appointment-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
        }

        .status-approved {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }

        .status-rejected {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }

        .status-completed {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            color: #0c5460;
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fc;
        }

        .appointment-date {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .appointment-time {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 500;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            padding: 12px;
            background: #f8f9fc;
            border-radius: 10px;
            border-left: 3px solid var(--primary-color);
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--dark-color);
        }

        .reason-section {
            background: #f8f9fc;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .reason-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
        }

        .reason-text {
            color: var(--secondary-color);
            line-height: 1.6;
        }

        .admin-notes {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            border-left: 3px solid var(--primary-color);
        }

        .admin-notes-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .admin-notes-text {
            color: var(--dark-color);
            line-height: 1.6;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(78,115,223,0.3);
        }

        .prediction-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 10px;
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
                <a class="nav-link active" href="index.php?route=user_appointments">
                    <i class="fas fa-calendar-check"></i>
                    My Appointments
                </a>
            </li>
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
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="text-white">
                            <i class="fas fa-calendar-check me-2"></i>My Appointments
                        </h2>
                        <a href="index.php?route=predict" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Prediction
                        </a>
                    </div>

                    <?php if (empty($appointments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3 class="mb-3">No Appointments Yet</h3>
                            <p class="text-muted mb-4">You haven't booked any consultations yet. Start by making a heart disease prediction to see if you need a consultation.</p>
                            <a href="index.php?route=predict" class="btn btn-primary">
                                <i class="fas fa-heartbeat me-2"></i>Make Prediction
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-card">
                                <div class="appointment-header">
                                    <div>
                                        <div class="appointment-date">
                                            <?php echo date('l, F d, Y', strtotime($appointment['appointment_date'])); ?>
                                            <?php if ($appointment['prediction_result']): ?>
                                                <span class="prediction-badge">High Risk</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="appointment-time">
                                            <i class="fas fa-clock me-2"></i>
                                            <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                        </div>
                                    </div>
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php if ($appointment['status'] === 'approved'): ?>
                                            <i class="fas fa-check-circle me-1"></i>Approved
                                        <?php else: ?>
                                            <?php echo ucfirst($appointment['status']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">Patient Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($appointment['patient_name']); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Email</div>
                                        <div class="info-value"><?php echo htmlspecialchars($appointment['patient_email']); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Phone</div>
                                        <div class="info-value"><?php echo htmlspecialchars($appointment['patient_phone']); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Booked On</div>
                                        <div class="info-value"><?php echo date('M d, Y g:i A', strtotime($appointment['created_at'])); ?></div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-label">Address</div>
                                    <div class="info-value"><?php echo htmlspecialchars($appointment['address']); ?></div>
                                </div>

                                <div class="reason-section">
                                    <div class="reason-label">
                                        <i class="fas fa-stethoscope me-2"></i>Reason for Consultation
                                    </div>
                                    <div class="reason-text"><?php echo htmlspecialchars($appointment['reason']); ?></div>
                                </div>

                                <?php if ($appointment['admin_notes']): ?>
                                    <div class="admin-notes">
                                        <div class="admin-notes-label">
                                            <i class="fas fa-comment me-2"></i>Admin Notes
                                        </div>
                                        <div class="admin-notes-text"><?php echo htmlspecialchars($appointment['admin_notes']); ?></div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($appointment['status'] === 'pending'): ?>
                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Your appointment is under review. We'll notify you once it's approved or if any changes are needed.
                                    </div>
                                <?php elseif ($appointment['status'] === 'approved'): ?>
                                    <div class="alert alert-success mt-3 mb-0">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Your appointment has been <u>approved</u>!</strong> Please arrive 10 minutes before your scheduled time. You will also receive a confirmation email.
                                    </div>
                                <?php elseif ($appointment['status'] === 'rejected'): ?>
                                    <div class="alert alert-warning mt-3 mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Your appointment was not approved. Please contact us for more information or book a new appointment.
                                    </div>
                                <?php elseif ($appointment['status'] === 'completed'): ?>
                                    <div class="alert alert-success mt-3 mb-0">
                                        <i class="fas fa-check-double me-2"></i>
                                        This appointment has been completed. Thank you for choosing our services!
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        $('#sidebarCollapse').on('click', function() {
            $('#sidebar').toggleClass('active');
        });
    </script>
</body>
</html> 