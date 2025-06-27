<?php
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Generate unique report ID
    $reportId = uniqid('REP_');
    
    // Create report record
    $stmt = $pdo->prepare("
        INSERT INTO reports (
            report_id, name, type, format, date_range, 
            start_date, end_date, include_charts, include_raw_data,
            created_by, created_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, NOW()
        )
    ");

    $reportName = ucwords(str_replace('_', ' ', $data['reportType'])) . ' Report - ' . date('Y-m-d');
    
    $stmt->execute([
        $reportId,
        $reportName,
        $data['reportType'],
        $data['format'],
        $data['dateRange'],
        $data['startDate'] ?: null,
        $data['endDate'] ?: null,
        $data['includeCharts'] ? 1 : 0,
        $data['includeRawData'] ? 1 : 0,
        $_SESSION['user_id']
    ]);

    // Generate report based on type
    $reportData = [];
    switch ($data['reportType']) {
        case 'prediction_summary':
            $reportData = generatePredictionSummary($pdo, $data);
            break;
        case 'user_activity':
            $reportData = generateUserActivity($pdo, $data);
            break;
        case 'dataset_analysis':
            $reportData = generateDatasetAnalysis($pdo, $data);
            break;
        case 'risk_analysis':
            $reportData = generateRiskAnalysis($pdo, $data);
            break;
        case 'custom':
            $reportData = generateCustomReport($pdo, $data);
            break;
        default:
            throw new Exception('Invalid report type');
    }

    // Generate file based on format
    $filePath = generateReportFile($reportData, $data['format'], $reportId);

    // Update report record with file path
    $stmt = $pdo->prepare("UPDATE reports SET file_path = ? WHERE report_id = ?");
    $stmt->execute([$filePath, $reportId]);

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'reportId' => $reportId,
        'reportName' => $reportName,
        'downloadUrl' => 'api/download_report.php?id=' . $reportId
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate report: ' . $e->getMessage()
    ]);
}

// Helper functions for generating different types of reports
function generatePredictionSummary($pdo, $data) {
    $dateCondition = getDateCondition($data);
    
    // Get prediction statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_predictions,
            SUM(CASE WHEN target = 1 THEN 1 ELSE 0 END) as high_risk_count,
            AVG(CASE WHEN target = 1 THEN 1 ELSE 0 END) * 100 as high_risk_percentage,
            AVG(confidence_score) as avg_confidence
        FROM predictions
        WHERE 1=1 $dateCondition
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get predictions by age group
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN age < 30 THEN 'Under 30'
                WHEN age BETWEEN 30 AND 40 THEN '30-40'
                WHEN age BETWEEN 41 AND 50 THEN '41-50'
                WHEN age BETWEEN 51 AND 60 THEN '51-60'
                ELSE 'Over 60'
            END as age_group,
            COUNT(*) as count,
            SUM(CASE WHEN target = 1 THEN 1 ELSE 0 END) as high_risk_count
        FROM predictions
        WHERE 1=1 $dateCondition
        GROUP BY age_group
        ORDER BY age_group
    ");
    $stmt->execute();
    $ageGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'type' => 'prediction_summary',
        'stats' => $stats,
        'ageGroups' => $ageGroups,
        'dateRange' => $data['dateRange'],
        'startDate' => $data['startDate'],
        'endDate' => $data['endDate']
    ];
}

function generateUserActivity($pdo, $data) {
    $dateCondition = getDateCondition($data);
    
    // Get user activity statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT user_id) as active_users,
            COUNT(*) as total_predictions,
            COUNT(DISTINCT DATE(created_at)) as active_days
        FROM predictions
        WHERE 1=1 $dateCondition
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get daily activity
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as predictions,
            COUNT(DISTINCT user_id) as users
        FROM predictions
        WHERE 1=1 $dateCondition
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->execute();
    $dailyActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'type' => 'user_activity',
        'stats' => $stats,
        'dailyActivity' => $dailyActivity,
        'dateRange' => $data['dateRange'],
        'startDate' => $data['startDate'],
        'endDate' => $data['endDate']
    ];
}

function generateDatasetAnalysis($pdo, $data) {
    // Get dataset statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_datasets,
            SUM(record_count) as total_records,
            AVG(record_count) as avg_records,
            COUNT(DISTINCT type) as dataset_types
        FROM dataset
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get datasets by type
    $stmt = $pdo->prepare("
        SELECT 
            type,
            COUNT(*) as count,
            SUM(record_count) as total_records
        FROM dataset
        GROUP BY type
        ORDER BY count DESC
    ");
    $stmt->execute();
    $datasetsByType = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'type' => 'dataset_analysis',
        'stats' => $stats,
        'datasetsByType' => $datasetsByType
    ];
}

function generateRiskAnalysis($pdo, $data) {
    $dateCondition = getDateCondition($data);
    
    // Get risk factor statistics
    $stmt = $pdo->prepare("
        SELECT 
            AVG(age) as avg_age,
            AVG(resting_bp) as avg_bp,
            AVG(cholesterol) as avg_cholesterol,
            AVG(max_heart_rate) as avg_heart_rate,
            AVG(st_depression) as avg_st_depression
        FROM predictions
        WHERE target = 1 AND 1=1 $dateCondition
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get risk factors by age group
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN age < 30 THEN 'Under 30'
                WHEN age BETWEEN 30 AND 40 THEN '30-40'
                WHEN age BETWEEN 41 AND 50 THEN '41-50'
                WHEN age BETWEEN 51 AND 60 THEN '51-60'
                ELSE 'Over 60'
            END as age_group,
            COUNT(*) as count,
            AVG(resting_bp) as avg_bp,
            AVG(cholesterol) as avg_cholesterol,
            AVG(max_heart_rate) as avg_heart_rate
        FROM predictions
        WHERE target = 1 AND 1=1 $dateCondition
        GROUP BY age_group
        ORDER BY age_group
    ");
    $stmt->execute();
    $riskByAge = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'type' => 'risk_analysis',
        'stats' => $stats,
        'riskByAge' => $riskByAge,
        'dateRange' => $data['dateRange'],
        'startDate' => $data['startDate'],
        'endDate' => $data['endDate']
    ];
}

function generateCustomReport($pdo, $data) {
    // Combine data from different report types based on selected options
    $reportData = [];
    
    if ($data['compareWithModel']) {
        $reportData['modelComparison'] = generateModelComparison($pdo, $data);
    }
    
    if ($data['includePatientDetails']) {
        $reportData['patientDetails'] = generatePatientDetails($pdo, $data);
    }
    
    if ($data['showConfidenceScores']) {
        $reportData['confidenceAnalysis'] = generateConfidenceAnalysis($pdo, $data);
    }
    
    if ($data['includeRecommendations']) {
        $reportData['recommendations'] = generateRecommendations($pdo, $data);
    }
    
    return [
        'type' => 'custom',
        'data' => $reportData,
        'options' => $data
    ];
}

// Helper function to get date condition for SQL queries
function getDateCondition($data) {
    $condition = '';
    
    if ($data['dateRange'] === 'custom' && $data['startDate'] && $data['endDate']) {
        $condition = " AND DATE(created_at) BETWEEN ? AND ?";
    } else {
        $days = intval($data['dateRange']);
        $condition = " AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
    }
    
    return $condition;
}

// Helper function to generate report file
function generateReportFile($data, $format, $reportId) {
    $fileName = $reportId . '_' . date('Y-m-d') . '.' . strtolower($format);
    $filePath = '../reports/' . $fileName;
    
    switch ($format) {
        case 'pdf':
            generatePDFReport($data, $filePath);
            break;
        case 'excel':
            generateExcelReport($data, $filePath);
            break;
        case 'csv':
            generateCSVReport($data, $filePath);
            break;
        case 'json':
            generateJSONReport($data, $filePath);
            break;
        default:
            throw new Exception('Unsupported report format');
    }
    
    return $fileName;
}

// Helper functions for generating different file formats
function generatePDFReport($data, $filePath) {
    // Implementation for PDF generation
    // This would use a library like TCPDF or FPDF
    // For now, we'll just create a placeholder file
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

function generateExcelReport($data, $filePath) {
    // Implementation for Excel generation
    // This would use a library like PhpSpreadsheet
    // For now, we'll just create a placeholder file
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

function generateCSVReport($data, $filePath) {
    // Implementation for CSV generation
    // For now, we'll just create a placeholder file
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

function generateJSONReport($data, $filePath) {
    // Implementation for JSON generation
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
} 