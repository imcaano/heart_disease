<?php
// Initialize user data and dashboard data
if (!isset($userData)) {
    $userData = [
        'username' => $_SESSION['username'] ?? 'Guest',
        'role' => $_SESSION['role'] ?? 'user',
        'wallet_address' => $_SESSION['wallet_address'] ?? ''
    ];
}

// Initialize dashboard data with default values
if (!isset($data)) {
    $data = [
        'total_users' => 0,
        'total_predictions' => 0,
        'positive_predictions' => 0,
        'negative_predictions' => 0,
        'recent_predictions' => [],
        'recent_users' => []
    ];
    
    // Try to get actual data if possible
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/../controllers/dashboard.php';
        if (function_exists('getDashboardData')) {
            $controllerData = getDashboardData();
            if (is_array($controllerData)) {
                $data = array_merge($data, $controllerData);
            }
        }
    }
}

class Prediction {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($userId, $data, $predictionResult, $transactionHash = null) {
        $sql = "INSERT INTO predictions (user_id, age, sex, cp, trestbps, chol, fbs, restecg, thalach, exang, oldpeak, slope, ca, thal, prediction_result, transaction_hash, created_at) 
                VALUES (:user_id, :age, :sex, :cp, :trestbps, :chol, :fbs, :restecg, :thalach, :exang, :oldpeak, :slope, :ca, :thal, :prediction_result, :transaction_hash, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':age' => $data['age'],
            ':sex' => $data['sex'],
            ':cp' => $data['cp'],
            ':trestbps' => $data['trestbps'],
            ':chol' => $data['chol'],
            ':fbs' => $data['fbs'],
            ':restecg' => $data['restecg'],
            ':thalach' => $data['thalach'],
            ':exang' => $data['exang'],
            ':oldpeak' => $data['oldpeak'],
            ':slope' => $data['slope'],
            ':ca' => $data['ca'],
            ':thal' => $data['thal'],
            ':prediction_result' => $predictionResult,
            ':transaction_hash' => $transactionHash
        ]);
    }

    public function getUserPredictions($userId, $limit = 10) {
        $sql = "SELECT * FROM predictions WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentPredictions($limit = 10) {
        $sql = "SELECT p.*, u.username 
                FROM predictions p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalPredictions() {
        $sql = "SELECT COUNT(*) as total FROM predictions";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getPositivePredictions() {
        $sql = "SELECT COUNT(*) as total FROM predictions WHERE prediction_result = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getNegativePredictions() {
        $sql = "SELECT COUNT(*) as total FROM predictions WHERE prediction_result = 0";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getUserStats($userId) {
        $sql = "SELECT 
                    COUNT(*) as total_predictions,
                    SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive_predictions,
                    SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative_predictions
                FROM predictions 
                WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 