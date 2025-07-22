<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
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

        .card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
            animation: fadeIn 0.5s ease-out forwards;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            transition: var(--transition);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78,115,223,0.2);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table th {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            padding: 0.5em 1em;
            border-radius: 6px;
        }

        .pagination {
            margin-top: 1rem;
        }

        .pagination .btn {
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
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
                <a class="nav-link active" href="index.php?route=manage_users">
                    <i class="fas fa-users"></i>
                    Manage Users
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Manage Users</h2>
            </div>

            <!-- Blockchain Info Banner -->
            <div class="alert alert-info mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Blockchain Restrictions</h6>
                        <p class="mb-0">This blockchain-based heart disease prediction system has certain restrictions. All user information is immutable and secured on the blockchain. Editing and deleting user data is not allowed to maintain data integrity and audit trail.</p>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- Will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fetch users data
        function fetchUsers() {
            $('#usersTableBody').html(`
                <tr>
                    <td colspan="2" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted mb-0">Loading user data...</p>
                    </td>
                </tr>
            `);
            
            fetch('api/get_users.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    updateUsersTable(data.users);
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                    $('#usersTableBody').html(`
                        <tr>
                            <td colspan="2" class="text-center py-4">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Failed to load user data: ${error.message}
                            </td>
                        </tr>
                    `);
                });
        }

        // Update users table
        function updateUsersTable(users) {
            const tbody = $('#usersTableBody');
            tbody.empty();
            
            if (!users || users.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="2" class="text-center py-4">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            No users found
                        </td>
                    </tr>
                `);
                return;
            }

            users.forEach(user => {
                const row = `
                    <tr>
                        <td>
                            <div class="text-truncate" style="max-width: 300px;">
                                <span class="text-monospace small">${user.username}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge ${user.role === 'admin' ? 'bg-primary' : 'bg-secondary'}">
                                ${user.role}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${user.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                ${user.status}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editUser('${user.username}')">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteUser('${user.username}')">Delete</button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Initial load
        $(document).ready(function() {
            fetchUsers();
        });

        // Blockchain restriction functions
        function showBlockchainRestriction(action, username) {
            const title = action === 'edit' ? 'Edit User' : 'Delete User';
            const message = action === 'edit' 
                ? 'User data cannot be edited in this blockchain-based heart disease prediction system. All user information is immutable and secured on the blockchain.'
                : 'User deletion is not allowed in this blockchain-based heart disease prediction system. All user data is permanently stored on the blockchain and cannot be deleted to maintain data integrity and audit trail.';
            
            const icon = action === 'edit' ? 'fa-edit' : 'fa-trash';
            const color = action === 'edit' ? 'warning' : 'danger';
            
            const modal = `
                <div class="modal fade" id="blockchainModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-${color} text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-block me-2"></i>
                                    Blockchain System
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <h6>${title}: ${username}</h6>
                                <div class="alert alert-${color} mt-3">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                        <div>
                                            <strong>Blockchain Restriction</strong>
                                            <p class="mb-0 mt-1">${message}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            $('#blockchainModal').remove();
            
            // Add new modal to body
            $('body').append(modal);
            
            // Show modal
            new bootstrap.Modal(document.getElementById('blockchainModal')).show();
        }

        function editUser(username) {
            showBlockchainRestriction('edit', username);
        }

        function deleteUser(username) {
            showBlockchainRestriction('delete', username);
        }
    </script>
</body>
</html> 