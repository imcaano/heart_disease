<?php
$page_title = "Reports";
require_once 'templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once 'templates/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Reports</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Report Generation Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Generate New Report</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?route=reports">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Report Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label">Report Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="prediction_summary">Prediction Summary</option>
                                    <option value="user_activity">User Activity</option>
                                    <option value="dataset_analysis">Dataset Analysis</option>
                                    <option value="risk_analysis">Risk Analysis</option>
                                    <option value="custom">Custom Report</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="format" class="form-label">Format</label>
                                <select class="form-select" id="format" name="format" required>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="date_range" class="form-label">Date Range</label>
                                <select class="form-select" id="date_range" name="date_range" required>
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="last_7_days">Last 7 Days</option>
                                    <option value="last_30_days">Last 30 Days</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3 custom-date-range" style="display: none;">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_charts" name="include_charts" checked>
                                    <label class="form-check-label" for="include_charts">
                                        Include Charts
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_raw_data" name="include_raw_data">
                                    <label class="form-check-label" for="include_raw_data">
                                        Include Raw Data
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
                    </form>
                </div>
            </div>

            <!-- Reports List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Generated Reports</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Type</th>
                                    <th>Format</th>
                                    <th>Date Range</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($report['name']); ?></td>
                                        <td><?php echo htmlspecialchars($report['type']); ?></td>
                                        <td><?php echo strtoupper(htmlspecialchars($report['format'])); ?></td>
                                        <td>
                                            <?php
                                            if ($report['date_range'] === 'custom') {
                                                echo date('M d, Y', strtotime($report['start_date'])) . ' - ' . date('M d, Y', strtotime($report['end_date']));
                                            } else {
                                                echo ucwords(str_replace('_', ' ', $report['date_range']));
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn btn-sm btn-primary" download>
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No reports generated yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateRangeSelect = document.getElementById('date_range');
    const customDateRange = document.querySelector('.custom-date-range');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    dateRangeSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateRange.style.display = 'flex';
            startDateInput.required = true;
            endDateInput.required = true;
        } else {
            customDateRange.style.display = 'none';
            startDateInput.required = false;
            endDateInput.required = false;
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?> 