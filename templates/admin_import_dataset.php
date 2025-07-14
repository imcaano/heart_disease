<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Dataset - Admin Dashboard</title>
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

        .import-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
            animation: fadeIn 0.5s ease-out forwards;
        }

        .import-card:hover {
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

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .btn-import {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            border: none;
            padding: 0.75rem 2rem;
            color: white;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-import:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78,115,223,0.2);
        }

        .btn-clear {
            background: linear-gradient(135deg, var(--secondary-color), #6e707e);
            border: none;
            padding: 0.75rem 2rem;
            color: white;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-clear:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(133,135,150,0.2);
        }

        .result-section {
            display: none;
            margin-top: 2rem;
            padding: 2rem;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--success-color), #169a6b);
            color: white;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--primary-color);
        }

        .file-upload-area {
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            background-color: rgba(78,115,223,0.05);
        }

        .file-upload-area:hover {
            background-color: rgba(78,115,223,0.1);
        }

        .file-upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .file-upload-text {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .file-upload-hint {
            color: var(--secondary-color);
            font-size: 0.875rem;
        }

        .file-preview {
            margin-top: 1rem;
            display: none;
        }

        .file-preview table {
            width: 100%;
            border-collapse: collapse;
        }

        .file-preview th, .file-preview td {
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .file-preview th {
            background-color: rgba(78,115,223,0.1);
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
                <a class="nav-link" href="index.php?route=admin_import_dataset">
                    <i class="fas fa-file-import"></i>
                    Import Dataset
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
                <h2 class="mb-0">Import Dataset</h2>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="import-card">
                        <form id="importForm" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label">Upload CSV or Excel File</label>
                                <input type="file" id="csvFile" name="csv_file" accept=".csv,.xlsx" class="form-control" required>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-import">
                                    <i class="fas fa-file-import me-2"></i>Import Dataset
                                </button>
                            </div>
                            <div id="importResult" class="mt-3"></div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="import-card">
                        <h5 class="mb-4">Import Instructions</h5>
                        <div class="import-instructions">
                            <h6 class="mb-3">CSV Format Requirements:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>File must be in CSV or Excel format</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>First row should contain column headers</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Required columns: age, sex, cp, trestbps, chol, fbs, restecg, thalach, exang, oldpeak, slope, ca, thal, target</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Maximum file size: 10MB</li>
                            </ul>
                            <div class="mt-4">
                                <h6 class="mb-3">Sample CSV Format:</h6>
                                <pre class="bg-light p-2 rounded"><code>age,sex,cp,trestbps,chol,fbs,restecg,thalach,exang,oldpeak,slope,ca,thal,target
63,1,3,145,233,1,0,150,0,2.3,0,0,1,1
37,1,2,130,250,0,1,187,0,3.5,3,0,2,0</code></pre>
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
        $(document).ready(function() {
            // File upload area click handler
            $('#fileUploadArea').on('click', function() {
                $('#csvFile').click();
            });
            
            // File selection handler
            $('#csvFile').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Show file name
                    $('.file-upload-text').text(file.name);
                    
                    // Preview CSV file
                    previewCSV(file);
                }
            });
            
            // Drag and drop handlers
            $('#fileUploadArea').on('dragover', function(e) {
                e.preventDefault();
                $(this).css('background-color', 'rgba(78,115,223,0.1)');
            });
            
            $('#fileUploadArea').on('dragleave', function(e) {
                e.preventDefault();
                $(this).css('background-color', 'rgba(78,115,223,0.05)');
            });
            
            $('#fileUploadArea').on('drop', function(e) {
                e.preventDefault();
                $(this).css('background-color', 'rgba(78,115,223,0.05)');
                
                const file = e.originalEvent.dataTransfer.files[0];
                if (file && (file.type === 'text/csv' || file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
                    $('#csvFile')[0].files = e.originalEvent.dataTransfer.files;
                    $('.file-upload-text').text(file.name);
                    
                    // Preview CSV file
                    previewCSV(file);
                } else {
                    alert('Please upload a CSV or Excel file');
                }
            });
            
            // Form submission handler
            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                $('#importResult').html('');
                const formData = new FormData(this);
                $.ajax({
                    url: 'index.php?route=import_predictions',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#importResult').html('<div class="alert alert-success">' + res.message + '</div>');
                        } else {
                            $('#importResult').html('<div class="alert alert-danger">' + (res.message || 'Import failed.') + '</div>');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Import failed.';
                        if (xhr.responseText) {
                            try {
                                const err = JSON.parse(xhr.responseText);
                                msg = err.message || msg;
                            } catch {}
                        }
                        $('#importResult').html('<div class="alert alert-danger">' + msg + '</div>');
                    }
                });
            });
            
            // Function to preview CSV file
            function previewCSV(file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const text = e.target.result;
                    const lines = text.split('\n');
                    
                    if (lines.length > 0) {
                        // Get headers
                        const headers = lines[0].split(',');
                        
                        // Create header row
                        let headerHtml = '<tr>';
                        headers.forEach(header => {
                            headerHtml += `<th>${header.trim()}</th>`;
                        });
                        headerHtml += '</tr>';
                        $('#previewHeader').html(headerHtml);
                        
                        // Create preview rows (up to 5 rows)
                        let bodyHtml = '';
                        const maxRows = Math.min(lines.length, 6); // Header + 5 data rows
                        
                        for (let i = 1; i < maxRows; i++) {
                            if (lines[i].trim() === '') continue;
                            
                            const cells = lines[i].split(',');
                            bodyHtml += '<tr>';
                            
                            cells.forEach(cell => {
                                bodyHtml += `<td>${cell.trim()}</td>`;
                            });
                            
                            bodyHtml += '</tr>';
                        }
                        
                        $('#previewBody').html(bodyHtml);
                        $('#filePreview').show();
                    }
                };
                
                reader.readAsText(file);
            }
        });
    </script>
</body>
</html> 