<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Reports - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="static/style.css" rel="stylesheet">
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

        .report-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-item {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .stat-item p {
            margin: 0;
            opacity: 0.8;
        }

        .table-responsive {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .table th {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .refresh-btn {
            background: linear-gradient(135deg, var(--success-color), #169a6b);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            transition: var(--transition);
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(28,200,138,0.2);
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

        .stat-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }

        .stat-card.positive {
            border-left-color: #ef4444;
        }

        .stat-card.negative {
            border-left-color: #22c55e;
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
            color: white;
        }

        .stat-icon.positive {
            background: #ef4444;
        }

        .stat-icon.negative {
            background: #22c55e;
        }

        .table-container {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .date-filter {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Prediction Reports</h2>
            </div>

            <!-- Summary Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3 id="totalPredictions">0</h3>
                    <p>Total Predictions</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card positive">
                        <div class="stat-icon positive">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3 id="positivePredictions">0</h3>
                        <p>Positive Cases</p>
                    </div>
                                </div>
                <div class="col-md-4">
                    <div class="stat-card negative">
                        <div class="stat-icon negative">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 id="negativePredictions">0</h3>
                        <p>Negative Cases</p>
                            </div>
                        </div>
                    </div>
                    
            <!-- Filter Form -->
            <form id="filterForm" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label>Date From</label>
                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                <div class="col-md-3">
                    <label>Date To</label>
                    <input type="date" class="form-control" name="end_date" required>
                                </div>
                <div class="col-md-3">
                    <label>Result</label>
                    <select class="form-select" name="prediction_result">
                        <option value="all">All</option>
                        <option value="positive">Positive</option>
                        <option value="negative">Negative</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <!-- Report Table -->
            <div id="reportTableContainer"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function fetchReports() {
        const params = $('#filterForm').serialize();
        $.get('index.php?route=get_report_data&' + params, function(data) {
            // Update summary stats
            if (data.stats) {
                $('#totalPredictions').text(data.stats.total_predictions || 0);
                $('#positivePredictions').text(data.stats.positive_cases || 0);
                $('#negativePredictions').text(data.stats.total_predictions - data.stats.positive_cases || 0);
            }

            // Update table
            let html = '<table class="table table-bordered table-striped">';
            html += '<thead><tr>';
            html += '<th>ID</th><th>Age</th><th>Sex</th><th>CP</th><th>BP</th><th>Chol</th><th>FBS</th><th>ECG</th><th>Thalach</th><th>Exang</th><th>Oldpeak</th><th>Slope</th><th>CA</th><th>Thal</th><th>Result</th><th>Date</th>';
            html += '</tr></thead><tbody>';
            
            if (data.reports && data.reports.length > 0) {
                data.reports.forEach(function(row) {
                    html += '<tr>';
                    html += `<td>${row.id}</td>`;
                    html += `<td>${row.age}</td>`;
                    html += `<td>${row.sex}</td>`;
                    html += `<td>${row.cp}</td>`;
                    html += `<td>${row.trestbps}</td>`;
                    html += `<td>${row.chol}</td>`;
                    html += `<td>${row.fbs}</td>`;
                    html += `<td>${row.restecg}</td>`;
                    html += `<td>${row.thalach}</td>`;
                    html += `<td>${row.exang}</td>`;
                    html += `<td>${row.oldpeak}</td>`;
                    html += `<td>${row.slope}</td>`;
                    html += `<td>${row.ca}</td>`;
                    html += `<td>${row.thal}</td>`;
                    html += `<td>${row.prediction_result == 1 ? 'Positive' : 'Negative'}</td>`;
                    html += `<td>${row.created_at}</td>`;
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="16" class="text-center">No data found</td></tr>';
            }
            html += '</tbody></table>';
            $('#reportTableContainer').html(html);
        }, 'json');
    }

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        fetchReports();
    });

    // Set default dates to last 30 days
    const today = new Date().toISOString().split('T')[0];
    const lastMonth = new Date();
    lastMonth.setDate(lastMonth.getDate() - 30);
    const lastMonthStr = lastMonth.toISOString().split('T')[0];
    $('[name=start_date]').val(lastMonthStr);
    $('[name=end_date]').val(today);

    // Initial load
    fetchReports();
    </script>
</body>
</html> 