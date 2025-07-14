<?php
function getDashboardStats($pdo, $user_id = null) {
    try {
        $stats = [
            'totalUsers' => 0,
            'totalPredictions' => 0,
            'accuracyRate' => 0,
            'positiveCount' => 0,
            'negativeCount' => 0,
            'recentUsers' => [],
            'recentPredictions' => []
        ];

        // Get total users
        $userQuery = "SELECT COUNT(*) as count FROM users";
        $stmt = $pdo->query($userQuery);
        $stats['totalUsers'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Get prediction stats
        $predictionQuery = $user_id ? 
            "SELECT COUNT(*) as total, 
                    SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive,
                    SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative,
                    AVG(CASE WHEN actual_result = predicted_result THEN 100 ELSE 0 END) as accuracy
             FROM predictions 
             WHERE user_id = ?" :
            "SELECT COUNT(*) as total, 
                    SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive,
                    SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative,
                    AVG(CASE WHEN actual_result = predicted_result THEN 100 ELSE 0 END) as accuracy
             FROM predictions";

        if ($user_id) {
            $stmt = $pdo->prepare($predictionQuery);
            $stmt->execute([$user_id]);
        } else {
            $stmt = $pdo->query($predictionQuery);
        }
        
        $predStats = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalPredictions'] = (int)$predStats['total'];
        $stats['positiveCount'] = (int)$predStats['positive'];
        $stats['negativeCount'] = (int)$predStats['negative'];
        $stats['accuracyRate'] = round($predStats['accuracy'] ?? 0, 2);

        // Get recent users (for admin)
        if (!$user_id) {
            $recentUsersQuery = "SELECT 
                u.id, u.username, u.wallet_address, u.role, u.status, u.created_at,
                COUNT(p.id) as prediction_count,
                COALESCE(AVG(CASE WHEN p.actual_result = p.predicted_result THEN 100 ELSE 0 END), 0) as accuracy
            FROM users u
            LEFT JOIN predictions p ON u.id = p.user_id
            GROUP BY u.id
            ORDER BY u.created_at DESC
            LIMIT 5";
            $stmt = $pdo->query($recentUsersQuery);
            $stats['recentUsers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Get recent predictions
        $recentPredQuery = $user_id ?
            "SELECT p.*, u.username 
             FROM predictions p 
             JOIN users u ON p.user_id = u.id 
             WHERE p.user_id = ? 
             ORDER BY p.created_at DESC 
             LIMIT 10" :
            "SELECT p.*, u.username 
             FROM predictions p 
             JOIN users u ON p.user_id = u.id 
             ORDER BY p.created_at DESC 
             LIMIT 10";

        if ($user_id) {
            $stmt = $pdo->prepare($recentPredQuery);
            $stmt->execute([$user_id]);
        } else {
            $stmt = $pdo->query($recentPredQuery);
        }
        $stats['recentPredictions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    } catch (PDOException $e) {
        error_log("Error in getDashboardStats: " . $e->getMessage());
        return false;
    }
} 