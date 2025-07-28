<?php
require_once 'config.php';

// Route handling
$route = $_GET['route'] ?? 'home';

switch ($route) {
    case 'home':
        require_once 'templates/index.php';
        break;
        
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Support both JSON and form-encoded
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (stripos($contentType, 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true);
                $username = $data['username'] ?? '';
                $password = $data['password'] ?? '';
                $wallet_address = $data['wallet_address'] ?? '';
            } else {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $wallet_address = $_POST['wallet_address'] ?? '';
            }
            
            // Debug information
            error_log("Login attempt - Username: $username, Wallet: $wallet_address");
            
            // Check if wallet address is provided
            if (empty($wallet_address)) {
                $error = 'Please connect your MetaMask wallet first';
                error_log("Login failed - No wallet address provided");
            } else {
                // Check if username exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
                if (!$user) {
                    $error = 'Username not found';
                    error_log("Login failed - Username not found: $username");
                } else if (!password_verify($password, $user['password'])) {
                    $error = 'Invalid password';
                    error_log("Login failed - Invalid password for username: $username");
                } else if (strtolower($user['wallet_address']) !== strtolower($wallet_address)) {
                    $error = 'Wallet address mismatch. Please use the same wallet address you used during signup';
                    error_log("Login failed - Wallet address mismatch. Expected: {$user['wallet_address']}, Got: $wallet_address");
                } else {
                    // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user'] = $user; // Store complete user data
                    
                    // Update last login time
                    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Log the login activity
                    $stmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, 'login', 'User logged in successfully', ?)");
                    $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);
                    
                    error_log("Login successful - Username: $username, Role: {$user['role']}");
                
                // Check if user is admin or developer and redirect accordingly
                if (in_array($user['role'], ['admin', 'developer'])) {
                    header('Location: index.php?route=admin_dashboard');
                } else {
                header('Location: index.php?route=dashboard');
                }
                exit();
                }
            }
        }
        require_once 'templates/login.php';
        break;
        
    case 'signup':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $wallet_address = $_POST['wallet_address'] ?? '';
            
            // Debug information
            error_log("Signup attempt - Username: $username, Email: $email, Wallet: $wallet_address");
            
            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists';
                error_log("Signup failed - Username already exists: $username");
            } else {
                // Check if email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email already registered';
                    error_log("Signup failed - Email already registered: $email");
            } else {
                // Check if wallet address exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE wallet_address = ?");
                $stmt->execute([$wallet_address]);
                if ($stmt->fetch()) {
                    $error = 'Wallet address already registered';
                        error_log("Signup failed - Wallet already registered: $wallet_address");
                } else {
                    // Create new user
                        try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, wallet_address, role) VALUES (?, ?, ?, ?, 'user')");
                    $stmt->execute([
                        $username,
                        $email,
                        password_hash($password, PASSWORD_DEFAULT),
                        $wallet_address
                    ]);
                            
                            // Log the signup activity
                            $userId = $pdo->lastInsertId();
                            $stmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, 'login', 'User registered successfully', ?)");
                            $stmt->execute([$userId, $_SERVER['REMOTE_ADDR']]);
                            
                            error_log("Signup successful - Username: $username, Email: $email, Wallet: $wallet_address");
                            
                            // Redirect to login page
                    header('Location: index.php?route=login');
                    exit();
                        } catch (PDOException $e) {
                            $error = 'Registration failed. Please try again.';
                            error_log("Signup failed - Database error: " . $e->getMessage());
                        }
                    }
                }
            }
        }
        require_once 'templates/signup.php';
        break;
        
    case 'dashboard':
        requireLogin();
        require_once 'controllers/dashboard.php';
        $dashboardData = getDashboardData();
        
        // Extract variables for the template
        $predictions = $dashboardData['predictions'] ?? [];
        $user_predictions = $dashboardData['user_predictions'] ?? 0;
        $user_positive_predictions = $dashboardData['user_positive_predictions'] ?? 0;
        $user_negative_predictions = $dashboardData['user_negative_predictions'] ?? 0;
        $user_recent_predictions = $dashboardData['user_recent_predictions'] ?? [];
        
        require_once 'templates/dashboard.php';
        break;
        
    case 'dataset':
        require_once 'controllers/dataset.php';
        break;
        
    case 'predict':
        // Check if it's a POST request for prediction
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                // Get JSON data from request body
                $json = file_get_contents('php://input');
                
                // Log the raw JSON data for debugging
                error_log("Raw JSON data received: " . $json);
                
                // Check if the JSON is valid
                if (empty($json)) {
                    throw new Exception("No JSON data received");
                }
                
                $data = json_decode($json, true);
                
                if ($data === null) {
                    $json_error = json_last_error_msg();
                    error_log("JSON decode error: " . $json_error);
                    throw new Exception("Invalid JSON data received: " . $json_error);
                }
                
                // Log the decoded data for debugging
                error_log("Decoded JSON data: " . print_r($data, true));
                
                // Required fields for prediction
                $required_fields = ['age', 'sex', 'cp', 'trestbps', 'chol', 'fbs', 'restecg', 
                                  'thalach', 'exang', 'oldpeak', 'slope', 'ca', 'thal'];
                
                // Validate all required fields are present
                foreach ($required_fields as $field) {
                    if (!isset($data[$field])) {
                        throw new Exception("Missing required field: $field");
                    }
                }
                
                // Convert data to Python-compatible format
                $python_data = json_encode($data);
                
                // Create a temporary file to pass data to Python
                $temp_dir = __DIR__ . '/temp';
                if (!file_exists($temp_dir)) {
                    mkdir($temp_dir, 0777, true);
                }
                
                $temp_file = $temp_dir . '/prediction_' . uniqid() . '.json';
                file_put_contents($temp_file, $python_data);
                
                // Use system Python instead of virtual environment
                $command = sprintf('python "%s" "%s" 2>&1', __DIR__ . '/predict.py', $temp_file);
                
                // Log the command
                error_log("Executing command: " . $command);
                
                // Execute Python script
                $output = shell_exec($command);
                
                // Clean up temp file
                unlink($temp_file);
                
                // Log raw output
                error_log("Raw Python output: " . $output);
                
                if ($output === null) {
                    throw new Exception("Failed to execute prediction script");
                }
                
                // Parse the JSON output
                $json_result = json_decode(trim($output), true);
                
                if ($json_result === null) {
                    error_log("Invalid JSON output: " . $output);
                    throw new Exception("No valid JSON found in prediction script output");
                }
                
                // If prediction was successful, store in database
                if ($json_result['success'] && isset($json_result['prediction'])) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO predictions (
                                user_id, age, sex, cp, trestbps, chol, fbs, restecg, 
                                thalach, exang, oldpeak, slope, ca, thal, prediction_result, 
                                transaction_hash, confidence_score, prediction, prediction_date
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                            )
                        ");
                        
                        $stmt->execute([
                            $_SESSION['user']['id'] ?? 0,
                            $data['age'],
                            $data['sex'],
                            $data['cp'],
                            $data['trestbps'],
                            $data['chol'],
                            $data['fbs'],
                            $data['restecg'],
                            $data['thalach'],
                            $data['exang'],
                            $data['oldpeak'],
                            $data['slope'],
                            $data['ca'],
                            $data['thal'],
                            $json_result['prediction'],
                            '', // Empty transaction hash
                            $json_result['probability'] ?? 0.00,
                            $json_result['prediction'] // Set prediction column same as prediction_result
                        ]);

                        // Update user's total predictions count
                        $stmt = $pdo->prepare("
                            UPDATE users 
                            SET total_predictions = total_predictions + 1 
                            WHERE id = ?
                        ");
                        $stmt->execute([$_SESSION['user']['id'] ?? 0]);

                        // Log the prediction activity
                        $stmt = $pdo->prepare("
                            INSERT INTO user_activity_log (user_id, activity_type, description) 
                            VALUES (?, 'prediction', 'New prediction made')
                        ");
                        $stmt->execute([$_SESSION['user']['id'] ?? 0]);

                    } catch (PDOException $e) {
                        error_log("Database error: " . $e->getMessage());
                        // Continue even if database storage fails
                    }
                }
                
                // Return the prediction result
                echo json_encode($json_result);
                exit;
                
            } catch (Exception $e) {
                error_log("Prediction error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        } else {
            // Display the prediction page for GET requests
            require_once 'templates/predict.php';
        }
        break;
        
    case 'profile':
        requireLogin();
        require_once 'templates/profile.php';
        break;
        
    case 'update_profile':
        require_once 'api/update_profile.php';
        break;
        
    case 'update_password':
        require_once 'api/update_password.php';
        break;
        
    case 'logout':
        session_destroy();
        header('Location: index.php');
        exit();
        break;
        
    case 'test_env':
        header('Content-Type: text/plain');
        $python_script = realpath(__DIR__ . '/api/test_env.py');
        $output = shell_exec("python \"$python_script\"");
        echo $output ?? 'No output from Python script';
        exit();
        break;
        
    case 'admin_dashboard':
        if (!isAdmin()) {
            header('Location: index.php?route=dashboard');
            exit;
        }
        require_once 'templates/admin_dashboard.php';
        break;

    case 'admin_appointments':
        if (!isAdmin()) {
            header('Location: index.php?route=dashboard');
            exit;
        }
        require_once 'templates/admin_appointments.php';
        break;

    case 'book_appointment':
        if (!isset($_SESSION['user'])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'User not logged in']);
                exit;
            } else {
                header('Location: index.php?route=login');
                exit;
            }
        }
        
        // If POST request, process the appointment booking
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'api/book_appointment.php';
        } else {
            // If GET request, show the booking form
            require_once 'templates/book_appointment.php';
        }
        break;

    case 'user_appointments':
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=login');
            exit;
        }
        require_once 'templates/user_appointments.php';
        break;

    case 'update_appointment_status':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        require_once 'api/update_appointment_status.php';
        break;

    case 'get_appointment_details':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        require_once 'api/get_appointment_details.php';
        break;

    case 'get_dashboard_data':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized access']);
            exit;
        }

        // Check if the admin_dashboard_stats table has recent data
        $stmt = $pdo->query("SELECT * FROM admin_dashboard_stats ORDER BY last_updated DESC LIMIT 1");
        $dashboardStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dashboardStats && strtotime($dashboardStats['last_updated']) > strtotime('-1 hour')) {
            // Use cached stats if recent
            $totalUsers = $dashboardStats['total_users'];
            $totalPredictions = $dashboardStats['total_predictions'];
            $totalPositivePredictions = $dashboardStats['total_positive_predictions'];
            $totalNegativePredictions = $dashboardStats['total_negative_predictions'];
            $expertAccuracyRate = $dashboardStats['expert_accuracy_rate'];
        } else {
            // Get fresh data directly from tables
        // Get total users
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get total predictions
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM predictions");
        $totalPredictions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get positive/negative predictions
            $stmt = $pdo->query("
                SELECT 
                    SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive,
                    SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative
                FROM predictions
            ");
            $predictionCounts = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalPositivePredictions = $predictionCounts['positive'] ?: 0;
            $totalNegativePredictions = $predictionCounts['negative'] ?: 0;
            
            // Try to update the cache
            try {
                if ($dashboardStats) {
                    $stmt = $pdo->prepare("
                        UPDATE admin_dashboard_stats 
                        SET total_users = ?, total_predictions = ?, 
                            total_positive_predictions = ?, total_negative_predictions = ?,
                            last_updated = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $totalUsers, 
                        $totalPredictions, 
                        $totalPositivePredictions, 
                        $totalNegativePredictions,
                        $dashboardStats['id']
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO admin_dashboard_stats 
                        (total_users, total_predictions, total_positive_predictions, total_negative_predictions)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $totalUsers, 
                        $totalPredictions, 
                        $totalPositivePredictions, 
                        $totalNegativePredictions
                    ]);
                }
            } catch (PDOException $e) {
                // Silently fail - we'll just continue without caching
            }
        }

        // Get accuracy rate from prediction_statistics
        $stmt = $pdo->query("
            SELECT AVG(accuracy_rate) as avg_accuracy 
            FROM prediction_statistics
        ");
        $accuracyResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $accuracyRate = $accuracyResult && $accuracyResult['avg_accuracy'] ? 
            round($accuracyResult['avg_accuracy'], 2) : 0;

        // If no accuracy rate from stats, calculate from predictions
        if ($accuracyRate == 0 && $totalPredictions > 0) {
        $stmt = $pdo->query("
            SELECT AVG(prediction_accuracy) as avg_accuracy 
            FROM users 
            WHERE total_predictions > 0
        ");
            $accuracyRate = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_accuracy'] ?: 0, 2);
        }

        // Get prediction trends (last 30 days) with more data
        $stmt = $pdo->query("
            SELECT 
                DATE(created_at) as date, 
                COUNT(*) as count,
                SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive,
                SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative
            FROM predictions
            WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        
        $predictionTrends = [
            'labels' => [],
            'values' => [],
            'positive' => $totalPositivePredictions,
            'negative' => $totalNegativePredictions
        ];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $predictionTrends['labels'][] = $row['date'];
            $predictionTrends['values'][] = (int)$row['count'];
        }

        // If no trends data found, create sample data for visualization
        if (empty($predictionTrends['labels'])) {
            $endDate = new DateTime();
            $startDate = (new DateTime())->modify('-30 days');
            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($startDate, $interval, $endDate);
            
            foreach ($dateRange as $date) {
                $predictionTrends['labels'][] = $date->format('Y-m-d');
                $predictionTrends['values'][] = 0;
            }
        }

        // Get recent users
        $stmt = $pdo->query("
            SELECT id, username, email, role, status
            FROM users
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dashboardData = [
            'totalUsers' => $totalUsers,
            'totalPredictions' => $totalPredictions,
            'accuracyRate' => $accuracyRate,
            'predictionTrends' => $predictionTrends,
            'recentUsers' => $recentUsers
        ];

        header('Content-Type: application/json');
        echo json_encode($dashboardData);
        exit;
        break;
        
    case 'manage_users':
        if (!isAdmin()) {
            header('Location: index.php?route=dashboard');
            exit;
        }
        require_once 'templates/manage_users.php';
        break;

    case 'get_users':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized access']);
            exit;
        }

        // Redirect to new API endpoint
        header('Location: api/get_users.php?' . http_build_query($_GET));
        exit;
        break;

    case 'get_user':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized access']);
            exit;
        }

        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, status, total_predictions, 
                   prediction_accuracy, last_login
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($user);
        exit;
        break;

    case 'update_user':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized access']);
            exit;
        }

        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? '';

        if (!in_array($role, ['user', 'expert', 'admin', 'developer']) || 
            !in_array($status, ['active', 'inactive', 'banned'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid role or status']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET role = ?, status = ? WHERE id = ?");
        $result = $stmt->execute([$role, $status, $userId]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'User updated successfully' : 'Failed to update user'
        ]);
        exit;
        break;

    case 'get_profile_data':
        if (!isset($_SESSION['user']['id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not logged in']);
            exit;
        }

        $userId = $_SESSION['user']['id'];
        
        // First check if prediction_statistics has an entry for this user
        $stmt = $pdo->prepare("
            SELECT * 
            FROM prediction_statistics 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $predictionStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($predictionStats) {
            $totalPredictions = $predictionStats['total_predictions'];
            $accuracyRate = $predictionStats['accuracy_rate'];
            $averageConfidence = $predictionStats['average_confidence'];
        } else {
            // Calculate from raw predictions
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_predictions,
                    AVG(confidence_score) as avg_confidence
                FROM predictions
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalPredictions = $result['total_predictions'] ?? 0;
            $averageConfidence = $result['avg_confidence'] ?? 0;
            
            // Get user accuracy from users table
            $stmt = $pdo->prepare("
                SELECT prediction_accuracy as accuracy_rate
                FROM users
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $userResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $accuracyRate = $userResult['accuracy_rate'] ?? 0;
        }
        
        // Format data
        $accuracyRate = round($accuracyRate, 2);
        $averageConfidence = round($averageConfidence * 100, 1);
        
        // Get recent activities from user_activity_log
        $stmt = $pdo->prepare("
            SELECT activity_type, description, created_at
            FROM user_activity_log
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $activityLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get recent predictions
        $stmt = $pdo->prepare("
            SELECT p.id, p.created_at, p.prediction_result, p.confidence_score,
                   p.verified_by_expert, p.expert_notes
            FROM predictions p
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
            LIMIT 3
        ");
        $stmt->execute([$userId]);
        $recentPredictions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get recent login activity
        $stmt = $pdo->prepare("
            SELECT last_login 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $lastLogin = $stmt->fetch(PDO::FETCH_ASSOC)['last_login'];
        
        // Format recent activity
        $recentActivity = [];
        
        // Add activity logs if available
        foreach ($activityLogs as $log) {
            $title = ucfirst(str_replace('_', ' ', $log['activity_type']));
            $recentActivity[] = [
                'type' => $log['activity_type'],
                'title' => $title,
                'description' => $log['description'] ?? $title,
                'timestamp' => $log['created_at']
            ];
        }
        
        // Add prediction activities if not already in activity logs
        if (empty($activityLogs)) {
            foreach ($recentPredictions as $prediction) {
                $result = $prediction['prediction_result'] == 1 ? 'Positive' : 'Negative';
                $confidence = round($prediction['confidence_score'] * 100);
                $recentActivity[] = [
                    'type' => 'prediction',
                    'title' => 'New Prediction',
                    'description' => "Heart disease prediction result: $result ($confidence% confidence)",
                    'timestamp' => $prediction['created_at']
                ];
            }
        }
        
        // Add login activity if available and not already in activity logs
        if ($lastLogin && empty($activityLogs)) {
            $recentActivity[] = [
                'type' => 'login',
                'title' => 'Account Login',
                'description' => 'Successfully logged in to your account',
                'timestamp' => $lastLogin
            ];
        }
        
        // Sort activities by timestamp (newest first)
        usort($recentActivity, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        // Get user reputation and expertise level
        $stmt = $pdo->prepare("
            SELECT reputation_score, expertise_level
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $profileData = [
            'totalPredictions' => $totalPredictions,
            'accuracyRate' => $accuracyRate,
            'confidenceScore' => $averageConfidence,
            'reputationScore' => $userStats['reputation_score'] ?? 0,
            'expertiseLevel' => $userStats['expertise_level'] ?? 'beginner',
            'recentActivity' => $recentActivity
        ];
        
        header('Content-Type: application/json');
        echo json_encode($profileData);
        exit;
        break;
        
    case 'admin_reports':
        if (!isAdmin()) {
            header('Location: index.php?route=dashboard');
            exit;
        }
        require_once 'templates/admin_reports.php';
        break;

    case 'reports':
        if (!isAdmin() && !isExpert()) {
            header('Location: index.php?route=dashboard');
            exit;
        }
        require_once 'templates/admin_reports.php';
        break;

    case 'admin_predict':
        if (!isAdmin()) {
            header('Location: index.php?route=predict');
            exit;
        }
        
        // Check if it's a POST request for prediction
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                // Get JSON data from request body
                $json = file_get_contents('php://input');
                
                // Log the raw JSON data for debugging
                error_log("Raw JSON data received: " . $json);
                
                // Check if the JSON is valid
                if (empty($json)) {
                    throw new Exception("No JSON data received");
                }
                
                $data = json_decode($json, true);
                
                if ($data === null) {
                    $json_error = json_last_error_msg();
                    error_log("JSON decode error: " . $json_error);
                    throw new Exception("Invalid JSON data received: " . $json_error);
                }
                
                // Log the decoded data for debugging
                error_log("Decoded JSON data: " . print_r($data, true));
                
                // Required fields for prediction
                $required_fields = ['age', 'sex', 'cp', 'trestbps', 'chol', 'fbs', 'restecg', 
                                  'thalach', 'exang', 'oldpeak', 'slope', 'ca', 'thal'];
                
                // Validate all required fields are present
                foreach ($required_fields as $field) {
                    if (!isset($data[$field])) {
                        throw new Exception("Missing required field: $field");
                    }
                }
                
                // Convert data to Python-compatible format
                $python_data = json_encode($data);
                
                // Create a temporary file to pass data to Python
                $temp_dir = __DIR__ . '/temp';
                if (!file_exists($temp_dir)) {
                    mkdir($temp_dir, 0777, true);
                }
                
                $temp_file = $temp_dir . '/prediction_' . uniqid() . '.json';
                file_put_contents($temp_file, $python_data);
                
                // Use system Python instead of virtual environment
                $command = sprintf('python "%s" "%s" 2>&1', __DIR__ . '/predict.py', $temp_file);
                
                // Log the command
                error_log("Executing command: " . $command);
                
                // Execute Python script
                $output = shell_exec($command);
                
                // Clean up temp file
                unlink($temp_file);
                
                // Log raw output
                error_log("Raw Python output: " . $output);
                
                if ($output === null) {
                    throw new Exception("Failed to execute prediction script");
                }
                
                // Parse the JSON output
                $json_result = json_decode(trim($output), true);
                
                if ($json_result === null) {
                    error_log("Invalid JSON output: " . $output);
                    throw new Exception("No valid JSON found in prediction script output");
                }
                
                // If prediction was successful, store in database
                if ($json_result['success'] && isset($json_result['prediction'])) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO predictions (
                                user_id, age, sex, cp, trestbps, chol, fbs, restecg, 
                                thalach, exang, oldpeak, slope, ca, thal, 
                                prediction_result, confidence_score, transaction_hash,
                                verified_by_expert
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1
                            )
                        ");
                        
                        $stmt->execute([
                            $_SESSION['user']['id'],
                            $data['age'],
                            $data['sex'],
                            $data['cp'],
                            $data['trestbps'],
                            $data['chol'],
                            $data['fbs'],
                            $data['restecg'],
                            $data['thalach'],
                            $data['exang'],
                            $data['oldpeak'],
                            $data['slope'],
                            $data['ca'],
                            $data['thal'],
                            $json_result['prediction'],
                            $json_result['probability'] ?? 0.00,
                            '' // Empty transaction hash for now
                        ]);

                        // Update user's total predictions count
                        $stmt = $pdo->prepare("
                            UPDATE users 
                            SET total_predictions = total_predictions + 1 
                            WHERE id = ?
                        ");
                        $stmt->execute([$_SESSION['user']['id']]);

                        // Log the prediction activity
                        $stmt = $pdo->prepare("
                            INSERT INTO user_activity_log (user_id, activity_type, description) 
                            VALUES (?, 'prediction', 'New prediction made by admin')
                        ");
                        $stmt->execute([$_SESSION['user']['id']]);

                        // Add success message to response
                        $json_result['message'] = 'Prediction saved successfully';

                    } catch (PDOException $e) {
                        error_log("Database error: " . $e->getMessage());
                        throw new Exception("Failed to save prediction to database: " . $e->getMessage());
                    }
                }
                
                // Return the prediction result
                echo json_encode($json_result);
                exit;
                
            } catch (Exception $e) {
                error_log("Prediction error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        } else {
            // Display the prediction page for GET requests
            require_once 'templates/admin_predict.php';
        }
        break;

    case 'admin_import_dataset':
        if (!isAdmin()) {
            header('Location: index.php?route=dashboard');
            exit;
        }
        require_once 'templates/admin_import_dataset.php';
        break;

    case 'import_dataset':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }

        // Handle file upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                // Validate file upload
                if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('No file uploaded or upload error occurred');
                }
                
                $file = $_FILES['csv_file'];
                $fileName = $file['name'];
                $fileSize = $file['size'];
                $fileTmpPath = $file['tmp_name'];
                $fileType = $file['type'];
                
                // Validate file type
                if ($fileType !== 'text/csv' && $fileType !== 'application/vnd.ms-excel') {
                    throw new Exception('Only CSV files are allowed');
                }
                
                // Validate file size (max 10MB)
                if ($fileSize > 10 * 1024 * 1024) {
                    throw new Exception('File size exceeds the limit of 10MB');
                }
                
                // Get dataset info
                $datasetName = $_POST['dataset_name'] ?? 'Unnamed Dataset';
                $datasetDescription = $_POST['dataset_description'] ?? '';
                $datasetType = $_POST['dataset_type'] ?? 'training';
                
                // Create datasets directory if it doesn't exist
                $datasetsDir = __DIR__ . '/datasets';
                if (!file_exists($datasetsDir)) {
                    mkdir($datasetsDir, 0755, true);
                }
                
                // Generate unique filename
                $uniqueFileName = uniqid() . '_' . $fileName;
                $targetFilePath = $datasetsDir . '/' . $uniqueFileName;
                
                // Move uploaded file
                if (!move_uploaded_file($fileTmpPath, $targetFilePath)) {
                    throw new Exception('Failed to save the uploaded file');
                }
                
                // Read CSV file to count records
                $csvFile = fopen($targetFilePath, 'r');
                if (!$csvFile) {
                    throw new Exception('Failed to open the uploaded file');
                }
                
                $headers = fgetcsv($csvFile);
                $recordCount = 0;
                
                while (fgetcsv($csvFile) !== false) {
                    $recordCount++;
                }
                
                fclose($csvFile);
                
                // Save dataset info to database
                $stmt = $pdo->prepare("
                    INSERT INTO dataset (name, description, type, file_path, record_count, created_by)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $datasetName,
                    $datasetDescription,
                    $datasetType,
                    $uniqueFileName,
                    $recordCount,
                    $_SESSION['user_id'] ?? 0
                ]);
                
                // Return success response
                echo json_encode([
                    'success' => true,
                    'message' => 'Dataset imported successfully',
                    'dataset_name' => $datasetName,
                    'records_imported' => $recordCount
                ]);
                
            } catch (Exception $e) {
                error_log("Dataset import error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit;
        }
        break;

    case 'admin_profile':
        if (!isAdmin()) {
            header('Location: index.php?route=profile');
            exit;
        }
        require_once 'templates/admin_profile.php';
        break;

    case 'get_report_data':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized access']);
            exit;
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $predictionResult = $_GET['prediction_result'] ?? 'all';

        // Get statistics for the period
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_predictions,
                SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive_cases,
                SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative_cases,
                AVG(confidence_score) as avg_confidence
            FROM predictions
            WHERE DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get prediction trends
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date, 
                COUNT(*) as count,
                SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive_count,
                SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative_count
            FROM predictions
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get detailed reports
        $query = "
            SELECT 
                p.id,
                u.username,
                p.age,
                p.sex,
                p.cp,
                p.trestbps,
                p.chol,
                p.fbs,
                p.restecg,
                p.thalach,
                p.exang,
                p.oldpeak,
                p.slope,
                p.ca,
                p.thal,
                p.prediction_result,
                p.created_at,
                p.confidence_score
            FROM predictions p
            JOIN users u ON p.user_id = u.id
            WHERE DATE(p.created_at) BETWEEN ? AND ?
        ";
        $params = [$startDate, $endDate];

        if ($predictionResult !== 'all') {
            $query .= " AND p.prediction_result = ?";
            $params[] = ($predictionResult === 'positive' ? 1 : 0);
        }

        $query .= " ORDER BY p.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'stats' => $stats,
            'trends' => $trends,
            'reports' => $reports
        ]);
        exit;
        break;
        
    case 'import_predictions':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            $filename = $_FILES['csv_file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $required = ['age','sex','cp','trestbps','chol','fbs','restecg','thalach','exang','oldpeak','slope','ca','thal','target'];
            $imported = 0;
            if ($ext === 'csv') {
                $handle = fopen($file, 'r');
                if (!$handle) {
                    echo json_encode(['success' => false, 'message' => 'Failed to open uploaded file.']);
                    exit;
                }
                $header = fgetcsv($handle);
                $header_map = array_map('strtolower', $header);
                $missing = array_diff($required, $header_map);
                if (count($missing) > 0) {
                    echo json_encode(['success' => false, 'message' => 'Missing columns: ' . implode(', ', $missing)]);
                    exit;
                }
                $col_indexes = array_flip($header_map);
                $pdo->beginTransaction();
                try {
                    while (($row = fgetcsv($handle)) !== false) {
                        $values = [];
                        foreach ($required as $col) {
                            $values[] = $row[$col_indexes[$col]];
                        }
                        $stmt = $pdo->prepare("INSERT INTO predictions (user_id, age, sex, cp, trestbps, chol, fbs, restecg, thalach, exang, oldpeak, slope, ca, thal, prediction_result, confidence_score) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $_SESSION['user']['id'] ?? 0,
                            $values[0], $values[1], $values[2], $values[3], $values[4], $values[5], $values[6], $values[7], $values[8], $values[9], $values[10], $values[11], $values[12], $values[13], 1.0
                        ]);
                        $imported++;
                    }
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => "Imported $imported records into predictions table."]);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
                }
                fclose($handle);
                exit;
            } elseif ($ext === 'xlsx') {
                require_once __DIR__ . '/vendor/autoload.php';
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
                $header = array_map('strtolower', $rows[0]);
                $missing = array_diff($required, $header);
                if (count($missing) > 0) {
                    echo json_encode(['success' => false, 'message' => 'Missing columns: ' . implode(', ', $missing)]);
                    exit;
                }
                $col_indexes = array_flip($header);
                $pdo->beginTransaction();
                try {
                    for ($i = 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        if (count(array_filter($row)) == 0) continue; // skip empty rows
                        $values = [];
                        foreach ($required as $col) {
                            $values[] = $row[$col_indexes[$col]];
                        }
                        $stmt = $pdo->prepare("INSERT INTO predictions (user_id, age, sex, cp, trestbps, chol, fbs, restecg, thalach, exang, oldpeak, slope, ca, thal, prediction_result, confidence_score) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $_SESSION['user']['id'] ?? 0,
                            $values[0], $values[1], $values[2], $values[3], $values[4], $values[5], $values[6], $values[7], $values[8], $values[9], $values[10], $values[11], $values[12], $values[13], 1.0
                        ]);
                        $imported++;
                    }
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => "Imported $imported records into predictions table."]);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
                }
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Unsupported file type. Please upload a CSV or Excel file.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            exit;
        }
        break;
        
    case 'save_prediction':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireLogin();
            
            header('Content-Type: application/json');
            
            $prediction_result = $_POST['prediction_result'] ?? null;
            $prediction_data = $_POST['prediction_data'] ?? null;
            $user_id = $_POST['user_id'] ?? $_SESSION['user']['id'];
            
            if ($prediction_result !== null && $prediction_data !== null) {
                try {
                    // Parse the prediction data to extract individual fields
                    $data = json_decode($prediction_data, true);
                    
                    if ($data) {
                        // Insert with individual fields matching the exact database schema
                        $stmt = $pdo->prepare("
                            INSERT INTO predictions (
                                user_id, age, sex, cp, trestbps, chol, fbs, restecg, 
                                thalach, exang, oldpeak, slope, ca, thal, prediction_result, 
                                transaction_hash, confidence_score, prediction, prediction_date
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        
                        $stmt->execute([
                            $user_id,
                            $data['age'] ?? 0,
                            $data['sex'] ?? 0,
                            $data['cp'] ?? 0,
                            $data['trestbps'] ?? 0,
                            $data['chol'] ?? 0,
                            $data['fbs'] ?? 0,
                            $data['restecg'] ?? 0,
                            $data['thalach'] ?? 0,
                            $data['exang'] ?? 0,
                            $data['oldpeak'] ?? 0,
                            $data['slope'] ?? 0,
                            $data['ca'] ?? 0,
                            $data['thal'] ?? 0,
                            $prediction_result,
                            '', // Empty transaction hash
                            0.85, // Default confidence score
                            $prediction_result // Set prediction column same as prediction_result
                        ]);
                        
                        $prediction_id = $pdo->lastInsertId();
                        
                        // Update user's total predictions count
                        $stmt = $pdo->prepare("
                            UPDATE users 
                            SET total_predictions = total_predictions + 1 
                            WHERE id = ?
                        ");
                        $stmt->execute([$user_id]);

                        // Log the prediction activity
                        $stmt = $pdo->prepare("
                            INSERT INTO user_activity_log (user_id, activity_type, description) 
                            VALUES (?, 'prediction', 'New prediction made')
                        ");
                        $stmt->execute([$user_id]);
                        
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Prediction saved successfully',
                            'prediction_id' => $prediction_id
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Invalid prediction data format']);
                    }
                } catch (PDOException $e) {
                    error_log("Error saving prediction: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Failed to save prediction: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing prediction data or result']);
            }
            exit;
        }
        break;
        
    default:
        header('HTTP/1.0 404 Not Found');
        require_once 'templates/404.php';
        break;
} 