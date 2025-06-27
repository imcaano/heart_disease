<?php
// View dataset template
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Dataset - Heart Disease Prediction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            padding: 20px;
        }
        .dataset-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .dataset-table {
            overflow-x: auto;
        }
        .back-button {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-white text-center mb-4">Heart Disease Prediction</h4>
                <a href="index.php"><i class="bi bi-house-door"></i> Home</a>
                <a href="index.php?route=dataset" class="active"><i class="bi bi-database"></i> Datasets</a>
                <a href="index.php?route=predict"><i class="bi bi-graph-up"></i> Predict</a>
                <a href="index.php?route=history"><i class="bi bi-clock-history"></i> History</a>
                <a href="index.php?route=profile"><i class="bi bi-person"></i> Profile</a>
                <a href="index.php?route=logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="back-button">
                    <a href="index.php?route=dataset" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Datasets
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Dataset Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="dataset-info">
                            <h5><?php echo htmlspecialchars($dataset['name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($dataset['description']); ?></p>
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Created:</strong> <?php echo date('F j, Y, g:i a', strtotime($dataset['created_at'])); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>File:</strong> <?php echo htmlspecialchars($dataset['file_name']); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Batch ID:</strong> <?php echo htmlspecialchars($dataset['batch_id']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="dataset-table">
                            <h5 class="mb-3">Records (Showing first 100)</h5>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Age</th>
                                        <th>Sex</th>
                                        <th>CP</th>
                                        <th>Trestbps</th>
                                        <th>Chol</th>
                                        <th>FBS</th>
                                        <th>RestECG</th>
                                        <th>Thalach</th>
                                        <th>Exang</th>
                                        <th>Oldpeak</th>
                                        <th>Slope</th>
                                        <th>CA</th>
                                        <th>Thal</th>
                                        <th>Target</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['age']); ?></td>
                                        <td><?php echo htmlspecialchars($record['sex']); ?></td>
                                        <td><?php echo htmlspecialchars($record['cp']); ?></td>
                                        <td><?php echo htmlspecialchars($record['trestbps']); ?></td>
                                        <td><?php echo htmlspecialchars($record['chol']); ?></td>
                                        <td><?php echo htmlspecialchars($record['fbs']); ?></td>
                                        <td><?php echo htmlspecialchars($record['restecg']); ?></td>
                                        <td><?php echo htmlspecialchars($record['thalach']); ?></td>
                                        <td><?php echo htmlspecialchars($record['exang']); ?></td>
                                        <td><?php echo htmlspecialchars($record['oldpeak']); ?></td>
                                        <td><?php echo htmlspecialchars($record['slope']); ?></td>
                                        <td><?php echo htmlspecialchars($record['ca']); ?></td>
                                        <td><?php echo htmlspecialchars($record['thal']); ?></td>
                                        <td><?php echo htmlspecialchars($record['target']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 