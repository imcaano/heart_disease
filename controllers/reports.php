<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?route=login');
    exit();
}

$error = '';
$success = '';
$reports = [];

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    try {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $format = $_POST['format'];
        $date_range = $_POST['date_range'];
        $include_charts = isset($_POST['include_charts']) ? 1 : 0;
        $include_raw_data = isset($_POST['include_raw_data']) ? 1 : 0;
        
        // Generate unique report ID
        $report_id = uniqid('REP_', true);
        
        // Set date range
        $start_date = null;
        $end_date = null;
        
        switch ($date_range) {
            case 'today':
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d');
                break;
            case 'yesterday':
                $start_date = date('Y-m-d', strtotime('-1 day'));
                $end_date = date('Y-m-d', strtotime('-1 day'));
                break;
            case 'last_7_days':
                $start_date = date('Y-m-d', strtotime('-7 days'));
                $end_date = date('Y-m-d');
                break;
            case 'last_30_days':
                $start_date = date('Y-m-d', strtotime('-30 days'));
                $end_date = date('Y-m-d');
                break;
            case 'this_month':
                $start_date = date('Y-m-01');
                $end_date = date('Y-m-t');
                break;
            case 'last_month':
                $start_date = date('Y-m-01', strtotime('-1 month'));
                $end_date = date('Y-m-t', strtotime('-1 month'));
                break;
            case 'custom':
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                break;
        }
        
        // Generate report based on type
        $report_data = [];
        switch ($type) {
            case 'prediction_summary':
                $report_data = generatePredictionSummary($start_date, $end_date);
                break;
            case 'user_activity':
                $report_data = generateUserActivity($start_date, $end_date);
                break;
            case 'dataset_analysis':
                $report_data = generateDatasetAnalysis($start_date, $end_date);
                break;
            case 'risk_analysis':
                $report_data = generateRiskAnalysis($start_date, $end_date);
                break;
            case 'custom':
                $report_data = generateCustomReport($_POST);
                break;
        }
        
        // Generate file based on format
        $file_path = generateReportFile($report_data, $format, $report_id, $include_charts);
        
        // Save report metadata to database
        $stmt = $pdo->prepare("INSERT INTO reports (report_id, name, type, format, date_range, start_date, end_date, include_charts, include_raw_data, file_path, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$report_id, $name, $type, $format, $date_range, $start_date, $end_date, $include_charts, $include_raw_data, $file_path, $_SESSION['user_id']]);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'report_generated', "Generated $type report: $name");
        
        $success = "Report generated successfully!";
        
    } catch (Exception $e) {
        $error = "Error generating report: " . $e->getMessage();
    }
}

// Fetch user's reports
try {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE created_by = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error fetching reports: " . $e->getMessage();
}

// Load reports view
require_once 'templates/reports.php';

// Helper functions for report generation
function generatePredictionSummary($start_date, $end_date) {
    global $pdo;
    
    $data = [
        'total_predictions' => 0,
        'positive_predictions' => 0,
        'negative_predictions' => 0,
        'accuracy' => 0,
        'predictions_by_date' => [],
        'risk_factors' => []
    ];
    
    // Get prediction statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN prediction = 1 THEN 1 ELSE 0 END) as positive,
            SUM(CASE WHEN prediction = 0 THEN 1 ELSE 0 END) as negative,
            AVG(CASE WHEN prediction = actual_result THEN 1 ELSE 0 END) * 100 as accuracy
        FROM predictions 
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $data['total_predictions'] = $stats['total'];
    $data['positive_predictions'] = $stats['positive'];
    $data['negative_predictions'] = $stats['negative'];
    $data['accuracy'] = round($stats['accuracy'], 2);
    
    // Get predictions by date
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count,
            SUM(CASE WHEN prediction = 1 THEN 1 ELSE 0 END) as positive_count
        FROM predictions 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['predictions_by_date'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get top risk factors
    $stmt = $pdo->prepare("
        SELECT 
            risk_factor,
            COUNT(*) as count,
            AVG(CASE WHEN prediction = 1 THEN 1 ELSE 0 END) * 100 as risk_percentage
        FROM predictions 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY risk_factor
        ORDER BY count DESC
        LIMIT 10
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['risk_factors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $data;
}

function generateUserActivity($start_date, $end_date) {
    global $pdo;
    
    $data = [
        'total_users' => 0,
        'active_users' => 0,
        'new_users' => 0,
        'predictions_by_user' => [],
        'activity_by_date' => []
    ];
    
    // Get user statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN last_login BETWEEN ? AND ? THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as new
        FROM users
    ");
    $stmt->execute([$start_date, $end_date, $start_date, $end_date]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $data['total_users'] = $stats['total'];
    $data['active_users'] = $stats['active'];
    $data['new_users'] = $stats['new'];
    
    // Get predictions by user
    $stmt = $pdo->prepare("
        SELECT 
            u.username,
            COUNT(p.id) as prediction_count,
            AVG(CASE WHEN p.prediction = p.actual_result THEN 1 ELSE 0 END) * 100 as accuracy
        FROM users u
        LEFT JOIN predictions p ON u.id = p.user_id
        WHERE p.created_at BETWEEN ? AND ?
        GROUP BY u.id, u.username
        ORDER BY prediction_count DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['predictions_by_user'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get activity by date
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count,
            COUNT(DISTINCT user_id) as unique_users
        FROM activity_log
        WHERE created_at BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['activity_by_date'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $data;
}

function generateDatasetAnalysis($start_date, $end_date) {
    global $pdo;
    
    $data = [
        'total_datasets' => 0,
        'total_records' => 0,
        'datasets_by_type' => [],
        'records_by_date' => [],
        'data_quality' => []
    ];
    
    // Get dataset statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(record_count) as total_records
        FROM dataset
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $data['total_datasets'] = $stats['total'];
    $data['total_records'] = $stats['total_records'];
    
    // Get datasets by type
    $stmt = $pdo->prepare("
        SELECT 
            type,
            COUNT(*) as count,
            SUM(record_count) as total_records
        FROM dataset
        WHERE created_at BETWEEN ? AND ?
        GROUP BY type
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['datasets_by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get records by date
    $stmt = $pdo->prepare("
        SELECT 
            DATE(d.created_at) as date,
            COUNT(d.id) as dataset_count,
            SUM(d.record_count) as record_count
        FROM dataset d
        WHERE d.created_at BETWEEN ? AND ?
        GROUP BY DATE(d.created_at)
        ORDER BY date
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['records_by_date'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get data quality metrics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_records,
            SUM(CASE WHEN age IS NULL THEN 1 ELSE 0 END) as missing_age,
            SUM(CASE WHEN sex IS NULL THEN 1 ELSE 0 END) as missing_sex,
            SUM(CASE WHEN cp IS NULL THEN 1 ELSE 0 END) as missing_cp,
            SUM(CASE WHEN trestbps IS NULL THEN 1 ELSE 0 END) as missing_trestbps,
            SUM(CASE WHEN chol IS NULL THEN 1 ELSE 0 END) as missing_chol,
            SUM(CASE WHEN fbs IS NULL THEN 1 ELSE 0 END) as missing_fbs,
            SUM(CASE WHEN restecg IS NULL THEN 1 ELSE 0 END) as missing_restecg,
            SUM(CASE WHEN thalach IS NULL THEN 1 ELSE 0 END) as missing_thalach,
            SUM(CASE WHEN exang IS NULL THEN 1 ELSE 0 END) as missing_exang,
            SUM(CASE WHEN oldpeak IS NULL THEN 1 ELSE 0 END) as missing_oldpeak,
            SUM(CASE WHEN slope IS NULL THEN 1 ELSE 0 END) as missing_slope,
            SUM(CASE WHEN ca IS NULL THEN 1 ELSE 0 END) as missing_ca,
            SUM(CASE WHEN thal IS NULL THEN 1 ELSE 0 END) as missing_thal,
            SUM(CASE WHEN target IS NULL THEN 1 ELSE 0 END) as missing_target
        FROM heart_data
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['data_quality'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $data;
}

function generateRiskAnalysis($start_date, $end_date) {
    global $pdo;
    
    $data = [
        'risk_factors' => [],
        'age_groups' => [],
        'gender_distribution' => [],
        'correlation_matrix' => []
    ];
    
    // Get risk factors analysis
    $stmt = $pdo->prepare("
        SELECT 
            AVG(age) as avg_age,
            AVG(trestbps) as avg_bp,
            AVG(chol) as avg_chol,
            AVG(thalach) as avg_heart_rate,
            AVG(oldpeak) as avg_st_depression,
            COUNT(CASE WHEN target = 1 THEN 1 END) * 100.0 / COUNT(*) as heart_disease_percentage
        FROM heart_data
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['risk_factors'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get age group distribution
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN age < 30 THEN 'Under 30'
                WHEN age BETWEEN 30 AND 40 THEN '30-40'
                WHEN age BETWEEN 41 AND 50 THEN '41-50'
                WHEN age BETWEEN 51 AND 60 THEN '51-60'
                WHEN age BETWEEN 61 AND 70 THEN '61-70'
                ELSE 'Over 70'
            END as age_group,
            COUNT(*) as count,
            AVG(CASE WHEN target = 1 THEN 1 ELSE 0 END) * 100 as heart_disease_percentage
        FROM heart_data
        WHERE created_at BETWEEN ? AND ?
        GROUP BY 
            CASE 
                WHEN age < 30 THEN 'Under 30'
                WHEN age BETWEEN 30 AND 40 THEN '30-40'
                WHEN age BETWEEN 41 AND 50 THEN '41-50'
                WHEN age BETWEEN 51 AND 60 THEN '51-60'
                WHEN age BETWEEN 61 AND 70 THEN '61-70'
                ELSE 'Over 70'
            END
        ORDER BY 
            CASE age_group
                WHEN 'Under 30' THEN 1
                WHEN '30-40' THEN 2
                WHEN '41-50' THEN 3
                WHEN '51-60' THEN 4
                WHEN '61-70' THEN 5
                ELSE 6
            END
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['age_groups'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get gender distribution
    $stmt = $pdo->prepare("
        SELECT 
            CASE WHEN sex = 1 THEN 'Male' ELSE 'Female' END as gender,
            COUNT(*) as count,
            AVG(CASE WHEN target = 1 THEN 1 ELSE 0 END) * 100 as heart_disease_percentage
        FROM heart_data
        WHERE created_at BETWEEN ? AND ?
        GROUP BY sex
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['gender_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate correlation matrix
    $stmt = $pdo->prepare("
        SELECT 
            AVG(age * trestbps) - AVG(age) * AVG(trestbps) as age_bp_corr,
            AVG(age * chol) - AVG(age) * AVG(chol) as age_chol_corr,
            AVG(age * thalach) - AVG(age) * AVG(thalach) as age_hr_corr,
            AVG(trestbps * chol) - AVG(trestbps) * AVG(chol) as bp_chol_corr,
            AVG(trestbps * thalach) - AVG(trestbps) * AVG(thalach) as bp_hr_corr,
            AVG(chol * thalach) - AVG(chol) * AVG(thalach) as chol_hr_corr
        FROM heart_data
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['correlation_matrix'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $data;
}

function generateCustomReport($params) {
    // This function will be implemented based on specific custom report requirements
    return [];
}

function generateReportFile($data, $format, $report_id, $include_charts) {
    $upload_dir = 'uploads/reports/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = $report_id . '.' . $format;
    $file_path = $upload_dir . $filename;
    
    switch ($format) {
        case 'pdf':
            generatePDFReport($data, $file_path, $include_charts);
            break;
        case 'excel':
            generateExcelReport($data, $file_path);
            break;
        case 'csv':
            generateCSVReport($data, $file_path);
            break;
        case 'json':
            generateJSONReport($data, $file_path);
            break;
    }
    
    return $file_path;
}

function generatePDFReport($data, $file_path, $include_charts) {
    // This function will be implemented using a PDF generation library
    // For now, we'll create a simple text file as a placeholder
    file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT));
}

function generateExcelReport($data, $file_path) {
    // This function will be implemented using a spreadsheet library
    // For now, we'll create a simple text file as a placeholder
    file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT));
}

function generateCSVReport($data, $file_path) {
    $fp = fopen($file_path, 'w');
    
    // Write headers
    fputcsv($fp, array_keys($data));
    
    // Write data
    fputcsv($fp, $data);
    
    fclose($fp);
}

function generateJSONReport($data, $file_path) {
    file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT));
} 