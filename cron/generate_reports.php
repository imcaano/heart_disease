<?php
require_once '../includes/db.php';

function generateDailyReports($db, $date) {
    // Generate reports for all users
    $sql = "INSERT INTO reports (
                user_id, report_type, total_predictions, positive_predictions, 
                negative_predictions, total_amount, total_users, success_rate,
                report_date, start_date, end_date
            )
            SELECT 
                p.user_id,
                'daily' as report_type,
                COUNT(*) as total_predictions,
                SUM(CASE WHEN p.prediction_result = 1 THEN 1 ELSE 0 END) as positive_predictions,
                SUM(CASE WHEN p.prediction_result = 0 THEN 1 ELSE 0 END) as negative_predictions,
                COALESCE(SUM(t.amount), 0) as total_amount,
                1 as total_users,
                (SUM(CASE WHEN p.prediction_result = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as success_rate,
                DATE(p.created_at) as report_date,
                ? as start_date,
                ? as end_date
            FROM predictions p
            LEFT JOIN transaction t ON p.transaction_hash = t.transaction_hash
            WHERE DATE(p.created_at) = ?
            GROUP BY p.user_id
            ON DUPLICATE KEY UPDATE
                total_predictions = VALUES(total_predictions),
                positive_predictions = VALUES(positive_predictions),
                negative_predictions = VALUES(negative_predictions),
                total_amount = VALUES(total_amount),
                success_rate = VALUES(success_rate)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$date, $date, $date]);

    // Generate admin summary report
    $sql = "INSERT INTO reports (
                user_id, report_type, total_predictions, positive_predictions, 
                negative_predictions, total_amount, total_users, success_rate,
                report_date, start_date, end_date
            )
            SELECT 
                (SELECT id FROM users WHERE role = 'admin' LIMIT 1) as user_id,
                'daily' as report_type,
                COUNT(*) as total_predictions,
                SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive_predictions,
                SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative_predictions,
                COALESCE(SUM(t.amount), 0) as total_amount,
                COUNT(DISTINCT p.user_id) as total_users,
                (SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as success_rate,
                ? as report_date,
                ? as start_date,
                ? as end_date
            FROM predictions p
            LEFT JOIN transaction t ON p.transaction_hash = t.transaction_hash
            WHERE DATE(p.created_at) = ?
            ON DUPLICATE KEY UPDATE
                total_predictions = VALUES(total_predictions),
                positive_predictions = VALUES(positive_predictions),
                negative_predictions = VALUES(negative_predictions),
                total_amount = VALUES(total_amount),
                total_users = VALUES(total_users),
                success_rate = VALUES(success_rate)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$date, $date, $date, $date]);
}

function generateWeeklyReports($db, $date) {
    $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($date)));
    $weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($date)));

    // Generate weekly reports for all users
    $sql = "INSERT INTO reports (
                user_id, report_type, total_predictions, positive_predictions, 
                negative_predictions, total_amount, total_users, success_rate,
                report_date, start_date, end_date
            )
            SELECT 
                p.user_id,
                'weekly' as report_type,
                COUNT(*) as total_predictions,
                SUM(CASE WHEN p.prediction_result = 1 THEN 1 ELSE 0 END) as positive_predictions,
                SUM(CASE WHEN p.prediction_result = 0 THEN 1 ELSE 0 END) as negative_predictions,
                COALESCE(SUM(t.amount), 0) as total_amount,
                1 as total_users,
                (SUM(CASE WHEN p.prediction_result = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as success_rate,
                ? as report_date,
                ? as start_date,
                ? as end_date
            FROM predictions p
            LEFT JOIN transaction t ON p.transaction_hash = t.transaction_hash
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY p.user_id
            ON DUPLICATE KEY UPDATE
                total_predictions = VALUES(total_predictions),
                positive_predictions = VALUES(positive_predictions),
                negative_predictions = VALUES(negative_predictions),
                total_amount = VALUES(total_amount),
                success_rate = VALUES(success_rate)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$weekEnd, $weekStart, $weekEnd, $weekStart, $weekEnd]);

    // Generate admin weekly summary
    $sql = "INSERT INTO reports (
                user_id, report_type, total_predictions, positive_predictions, 
                negative_predictions, total_amount, total_users, success_rate,
                report_date, start_date, end_date
            )
            SELECT 
                (SELECT id FROM users WHERE role = 'admin' LIMIT 1) as user_id,
                'weekly' as report_type,
                COUNT(*) as total_predictions,
                SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive_predictions,
                SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative_predictions,
                COALESCE(SUM(t.amount), 0) as total_amount,
                COUNT(DISTINCT p.user_id) as total_users,
                (SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as success_rate,
                ? as report_date,
                ? as start_date,
                ? as end_date
            FROM predictions p
            LEFT JOIN transaction t ON p.transaction_hash = t.transaction_hash
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            ON DUPLICATE KEY UPDATE
                total_predictions = VALUES(total_predictions),
                positive_predictions = VALUES(positive_predictions),
                negative_predictions = VALUES(negative_predictions),
                total_amount = VALUES(total_amount),
                total_users = VALUES(total_users),
                success_rate = VALUES(success_rate)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$weekEnd, $weekStart, $weekEnd, $weekStart, $weekEnd]);
}

function generateMonthlyReports($db, $date) {
    $monthStart = date('Y-m-01', strtotime($date));
    $monthEnd = date('Y-m-t', strtotime($date));

    // Generate monthly reports for all users
    $sql = "INSERT INTO reports (
                user_id, report_type, total_predictions, positive_predictions, 
                negative_predictions, total_amount, total_users, success_rate,
                report_date, start_date, end_date
            )
            SELECT 
                p.user_id,
                'monthly' as report_type,
                COUNT(*) as total_predictions,
                SUM(CASE WHEN p.prediction_result = 1 THEN 1 ELSE 0 END) as positive_predictions,
                SUM(CASE WHEN p.prediction_result = 0 THEN 1 ELSE 0 END) as negative_predictions,
                COALESCE(SUM(t.amount), 0) as total_amount,
                1 as total_users,
                (SUM(CASE WHEN p.prediction_result = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as success_rate,
                ? as report_date,
                ? as start_date,
                ? as end_date
            FROM predictions p
            LEFT JOIN transaction t ON p.transaction_hash = t.transaction_hash
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY p.user_id
            ON DUPLICATE KEY UPDATE
                total_predictions = VALUES(total_predictions),
                positive_predictions = VALUES(positive_predictions),
                negative_predictions = VALUES(negative_predictions),
                total_amount = VALUES(total_amount),
                success_rate = VALUES(success_rate)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$monthEnd, $monthStart, $monthEnd, $monthStart, $monthEnd]);

    // Generate admin monthly summary
    $sql = "INSERT INTO reports (
                user_id, report_type, total_predictions, positive_predictions, 
                negative_predictions, total_amount, total_users, success_rate,
                report_date, start_date, end_date
            )
            SELECT 
                (SELECT id FROM users WHERE role = 'admin' LIMIT 1) as user_id,
                'monthly' as report_type,
                COUNT(*) as total_predictions,
                SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive_predictions,
                SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative_predictions,
                COALESCE(SUM(t.amount), 0) as total_amount,
                COUNT(DISTINCT p.user_id) as total_users,
                (SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as success_rate,
                ? as report_date,
                ? as start_date,
                ? as end_date
            FROM predictions p
            LEFT JOIN transaction t ON p.transaction_hash = t.transaction_hash
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            ON DUPLICATE KEY UPDATE
                total_predictions = VALUES(total_predictions),
                positive_predictions = VALUES(positive_predictions),
                negative_predictions = VALUES(negative_predictions),
                total_amount = VALUES(total_amount),
                total_users = VALUES(total_users),
                success_rate = VALUES(success_rate)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$monthEnd, $monthStart, $monthEnd, $monthStart, $monthEnd]);
}

try {
    $date = date('Y-m-d');
    
    // Generate daily reports
    generateDailyReports($db, $date);
    
    // Generate weekly reports on Sunday
    if (date('w', strtotime($date)) == 0) {
        generateWeeklyReports($db, $date);
    }
    
    // Generate monthly reports on last day of month
    if (date('t', strtotime($date)) == date('d', strtotime($date))) {
        generateMonthlyReports($db, $date);
    }
    
    echo "Reports generated successfully\n";
} catch (PDOException $e) {
    echo "Error generating reports: " . $e->getMessage() . "\n";
} 