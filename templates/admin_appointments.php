<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?route=login');
    exit;
}

// Get appointments from database
require_once 'config.php';
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = '';
if ($status_filter && $status_filter !== 'all') {
    $where_clause = "WHERE a.status = :status";
}

// Get appointments with user details
$query = "
    SELECT a.*, u.username, u.email as user_email, p.prediction as prediction_result
    FROM appointments a
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN predictions p ON a.prediction_id = p.id
    $where_clause
    ORDER BY a.created_at DESC
";

$stmt = $pdo->prepare($query);
if ($status_filter && $status_filter !== 'all') {
    $stmt->bindParam(':status', $status_filter);
}
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM appointments
";
$stats_stmt = $pdo->query($stats_query);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            background-color: var(--light-color);
            color: var(--dark-color);
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
            display: flex;
            flex-direction: column;
        }

        #sidebar ul.nav {
            flex: 1 1 auto;
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

        .stat-card.pending {
            border-left: 4px solid var(--warning-color);
        }

        .stat-card.approved {
            border-left: 4px solid var(--success-color);
        }

        .stat-card.rejected {
            border-left: 4px solid var(--danger-color);
        }

        .stat-card.completed {
            border-left: 4px solid var(--primary-color);
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

        .stat-icon.pending {
            background: var(--warning-color);
        }

        .stat-icon.approved {
            background: var(--success-color);
        }

        .stat-icon.rejected {
            background: var(--danger-color);
        }

        .stat-icon.completed {
            background: var(--primary-color);
        }

        .appointment-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .appointment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            transition: var(--transition);
        }

        .btn-approve {
            background: var(--success-color);
            color: white;
        }

        .btn-reject {
            background: var(--danger-color);
            color: white;
        }

        .btn-complete {
            background: var(--primary-color);
            color: white;
        }

        .filter-section {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-body {
            padding: 30px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f8f9fc;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark-color);
        }

        .info-value {
            color: var(--secondary-color);
        }
        .profile-section {
            margin-top: auto;
            padding: 20px 20px 30px 20px;
            background: rgba(255,255,255,0.03);
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .profile-img {
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 20px;
            color: #fff;
        }
        .logout-btn {
            margin-top: 15px;
            width: 100%;
            background: var(--danger-color);
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            padding: 10px 0;
            transition: background 0.2s, color 0.2s;
        }
        .logout-btn:hover {
            background: #c82333; /* darker red for hover */
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="text-white mb-0">Heart Disease</h4>
            <small class="text-white-50">Admin Panel</small>
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
                <a class="nav-link active" href="index.php?route=admin_appointments">
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
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=admin_profile">
                    <i class="fas fa-user-cog"></i>
                    Profile
                </a>
            </li>
        </ul>

        <div class="profile-section">
            <div class="d-flex align-items-center mb-2">
                <div class="profile-img">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div class="text-white"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
                    <small class="text-white-50">Administrator</small>
                </div>
            </div>
            <a href="index.php?route=logout" class="btn logout-btn">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-check me-2"></i>Manage Appointments</h2>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card pending">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3><?php echo $stats['pending']; ?></h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card approved">
                    <div class="stat-icon approved">
                        <i class="fas fa-check"></i>
                    </div>
                    <h3><?php echo $stats['approved']; ?></h3>
                    <p class="mb-0">Approved</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card rejected">
                    <div class="stat-icon rejected">
                        <i class="fas fa-times"></i>
                    </div>
                    <h3><?php echo $stats['rejected']; ?></h3>
                    <p class="mb-0">Rejected</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card completed">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <h3><?php echo $stats['completed']; ?></h3>
                    <p class="mb-0">Completed</p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Filter Appointments</h5>
                </div>
                <div class="col-md-6 text-end">
                    <select class="form-select" id="statusFilter" onchange="filterAppointments()">
                        <option value="all">All Appointments</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Appointments List -->
        <div id="appointmentsList">
            <?php if (empty($appointments)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No appointments found</h4>
                    <p class="text-muted">There are no appointments matching your current filter.</p>
                </div>
            <?php else: ?>
                <?php foreach ($appointments as $appointment): ?>
                    <div class="appointment-card">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <div class="mb-1">
                                    <strong>Date:</strong> <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?>
                                </div>
                                <div>
                                    <strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                                <?php if ($appointment['prediction_result']): ?>
                                    <br>
                                    <small class="text-danger">High Risk Prediction</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <strong>Address:</strong><br>
                                    <?php echo htmlspecialchars(substr($appointment['address'], 0, 80)) . (strlen($appointment['address']) > 80 ? '...' : ''); ?>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <strong>Reason:</strong><br>
                                    <?php echo htmlspecialchars(substr($appointment['reason'], 0, 80)) . (strlen($appointment['reason']) > 80 ? '...' : ''); ?>
                                </small>
                            </div>
                            <div class="col-md-2 text-end">
                                <?php if ($appointment['status'] === 'pending'): ?>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-action btn-approve" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'approved')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-action btn-reject" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'rejected')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                <?php elseif ($appointment['status'] === 'approved'): ?>
                                    <button class="btn btn-action btn-complete" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'completed')">
                                        <i class="fas fa-check-double"></i> Complete
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Appointment Details Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-check me-2"></i>Appointment Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="appointmentModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <div id="statusSuccessAlert" style="display:none;" class="alert alert-success text-center mb-4">Appointment status updated successfully!</div>
    <div id="statusErrorAlert" style="display:none;" class="alert alert-danger text-center mb-4"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterAppointments() {
            const status = document.getElementById('statusFilter').value;
            window.location.href = `index.php?route=admin_appointments&status=${status}`;
        }

        function viewAppointment(appointmentId) {
            $.ajax({
                url: 'index.php?route=get_appointment_details',
                method: 'POST',
                data: { appointment_id: appointmentId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#appointmentModalBody').html(response.html);
                        $('#appointmentModal').modal('show');
                    } else {
                        alert('Failed to load appointment details.');
                    }
                },
                error: function() {
                    alert('An error occurred while loading appointment details.');
                }
            });
        }

        function updateStatus(appointmentId, status) {
            if (!confirm(`Are you sure you want to ${status} this appointment?`)) {
                return;
            }
            document.getElementById('statusSuccessAlert').style.display = 'none';
            document.getElementById('statusErrorAlert').style.display = 'none';
            $.ajax({
                url: 'api/update_appointment_status.php',
                method: 'POST',
                contentType: 'application/json',
                headers: {
                    'Authorization': 'Bearer ' + ADMIN_TOKEN
                },
                data: JSON.stringify({
                    appointment_id: appointmentId,
                    status: status
                }),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        document.getElementById('statusSuccessAlert').style.display = 'block';
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        document.getElementById('statusErrorAlert').textContent = response.message || 'Failed to update appointment status.';
                        document.getElementById('statusErrorAlert').style.display = 'block';
                    }
                },
                error: function(xhr, status, error) {
                    document.getElementById('statusErrorAlert').textContent = 'An error occurred while updating appointment status.';
                    document.getElementById('statusErrorAlert').style.display = 'block';
                    console.error('AJAX Error:', { xhr, status, error });
                }
            });
        }
    </script>
    <script>
        const ADMIN_TOKEN = "<?php echo htmlspecialchars($_SESSION['user']['token'] ?? ''); ?>";
        console.log('ADMIN_TOKEN:', ADMIN_TOKEN); // Debug: Remove after confirming token is present
    </script>
</body>
</html> 