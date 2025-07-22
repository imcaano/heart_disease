<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Dataset - Heart Disease Prediction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --sidebar-width: 250px;
        }
        body {
            background: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px;
            transition: all 0.3s;
        }
        .sidebar-header {
            padding: 20px 0;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .card-header {
            background: white;
            border-bottom: none;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: var(--primary-color);
            background: rgba(30, 60, 114, 0.05);
        }
        .upload-area i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        .wallet-info {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        .wallet-address {
            font-family: monospace;
            word-break: break-all;
        }
        .dataset-list {
            margin-top: 30px;
        }
        .dataset-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dataset-info {
            flex: 1;
        }
        .dataset-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .dataset-meta {
            font-size: 0.9rem;
            color: #666;
        }
        .dataset-actions {
            display: flex;
            gap: 10px;
        }
        .btn-icon {
            width: 35px;
            height: 35px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        .nav-tabs {
            border-bottom: none;
            margin-bottom: 20px;
        }
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            padding: 10px 20px;
            border-radius: 10px 10px 0 0;
            margin-right: 5px;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background: white;
            border-bottom: 3px solid var(--primary-color);
        }
        .tab-content {
            background: white;
            border-radius: 0 0 15px 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Heart Disease Prediction</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=predict">
                    <i class="fas fa-heartbeat"></i> Predict
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="index.php?route=dataset">
                    <i class="fas fa-database"></i> Import Dataset
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=profile">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?route=logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
        <div class="wallet-info">
            <div class="d-flex align-items-center mb-2">
                <i class="fab fa-ethereum me-2"></i>
                <span>Connected Wallet</span>
            </div>
            <div class="wallet-address">
                <?php echo htmlspecialchars($_SESSION['wallet_address']); ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-center">Dataset Management</h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>

                            <?php if (isset($success)): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <!-- Tabs -->
                            <ul class="nav nav-tabs" id="datasetTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="import-tab" data-bs-toggle="tab" data-bs-target="#import" type="button" role="tab" aria-controls="import" aria-selected="true">
                                        <i class="fas fa-upload me-2"></i>Import Dataset
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab" aria-controls="list" aria-selected="false">
                                        <i class="fas fa-list me-2"></i>Your Datasets
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="datasetTabsContent">
                                <!-- Import Tab -->
                                <div class="tab-pane fade show active" id="import" role="tabpanel" aria-labelledby="import-tab">
                                    <form method="POST" action="api/direct_import.php" enctype="multipart/form-data" id="uploadForm">
                                        <div class="upload-area" id="dropZone">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <h4>Drag & Drop or Click to Upload</h4>
                                            <p class="text-muted">Supported formats: CSV</p>
                                            <input type="file" name="csv_file" id="fileInput" class="d-none" accept=".csv">
                                        </div>

                                        <div class="mb-3 mt-4">
                                            <label for="name" class="form-label">Dataset Name</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="type" class="form-label">Dataset Type</label>
                                            <input type="text" class="form-control" id="type" name="type" value="Testing Dataset" readonly>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Import Dataset</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- List Tab -->
                                <div class="tab-pane fade" id="list" role="tabpanel" aria-labelledby="list-tab">
                                    <div class="dataset-list">
                                        <?php if (empty($datasets)): ?>
                                            <div class="text-center py-5">
                                                <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No datasets imported yet.</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($datasets as $dataset): ?>
                                                <div class="dataset-item">
                                                    <div class="dataset-info">
                                                        <div class="dataset-name"><?php echo htmlspecialchars($dataset['name']); ?></div>
                                                        <div class="dataset-meta">
                                                            Uploaded on <?php echo date('Y-m-d H:i', strtotime($dataset['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="dataset-actions">
                                                        <button class="btn btn-icon btn-outline-primary" title="View" onclick="viewDataset(<?php echo $dataset['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-icon btn-outline-danger" title="Delete" onclick="deleteDataset(<?php echo $dataset['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
        // Initialize Bootstrap tabs
        document.addEventListener('DOMContentLoaded', function() {
            // Get the tab elements
            const importTab = document.getElementById('import-tab');
            const listTab = document.getElementById('list-tab');
            
            // Add click event listeners
            importTab.addEventListener('click', function() {
                this.classList.add('active');
                listTab.classList.remove('active');
                document.getElementById('import').classList.add('show', 'active');
                document.getElementById('list').classList.remove('show', 'active');
            });
            
            listTab.addEventListener('click', function() {
                this.classList.add('active');
                importTab.classList.remove('active');
                document.getElementById('list').classList.add('show', 'active');
                document.getElementById('import').classList.remove('show', 'active');
            });
        });

        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const uploadForm = document.getElementById('uploadForm');

        // Handle drag and drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--primary-color)';
            dropZone.style.background = 'rgba(30, 60, 114, 0.05)';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = '#ddd';
            dropZone.style.background = 'none';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#ddd';
            dropZone.style.background = 'none';
            
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                uploadForm.submit();
            }
        });

        // Handle click to upload
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                uploadForm.submit();
            }
        });

        // Dataset actions
        function viewDataset(id) {
            window.location.href = 'index.php?route=dataset&action=view&id=' + id;
        }

        function deleteDataset(id) {
            if (confirm('Are you sure you want to delete this dataset? This action cannot be undone.')) {
                window.location.href = 'index.php?route=dataset&action=delete&id=' + id;
            }
        }

        // Check MetaMask connection
        if (typeof window.ethereum !== 'undefined') {
            window.ethereum.on('accountsChanged', function (accounts) {
                if (accounts.length === 0) {
                    window.location.href = 'index.php?route=login';
                }
            });
        }
    </script>
</body>
</html> 