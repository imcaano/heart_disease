-- Drop tables if they exist
DROP TABLE IF EXISTS user_activity_log;
DROP TABLE IF EXISTS predictions;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    wallet_address VARCHAR(42) UNIQUE,
    role ENUM('user', 'admin', 'expert', 'developer') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    total_predictions INT DEFAULT 0,
    prediction_accuracy DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Create predictions table
CREATE TABLE predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    age INT NOT NULL,
    sex TINYINT NOT NULL,
    cp TINYINT NOT NULL,
    trestbps INT NOT NULL,
    chol INT NOT NULL,
    fbs TINYINT NOT NULL,
    restecg TINYINT NOT NULL,
    thalach INT NOT NULL,
    exang TINYINT NOT NULL,
    oldpeak DECIMAL(3,1) NOT NULL,
    slope TINYINT NOT NULL,
    ca TINYINT NOT NULL,
    thal TINYINT NOT NULL,
    predicted_outcome TINYINT NOT NULL,
    actual_outcome TINYINT NULL,
    prediction_result ENUM('high', 'medium', 'low') NOT NULL,
    confidence_score DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create user activity log table
CREATE TABLE user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample users
INSERT INTO users (username, email, password, role, status) VALUES
('admin', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('expert1', 'expert1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'expert', 'active'),
('user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active');

-- Insert sample predictions
INSERT INTO predictions (user_id, age, sex, cp, trestbps, chol, fbs, restecg, thalach, exang, oldpeak, slope, ca, thal, predicted_outcome, actual_outcome, prediction_result, confidence_score, created_at) VALUES
(1, 63, 1, 3, 145, 233, 1, 0, 150, 0, 2.3, 0, 0, 1, 1, 1, 'high', 0.89, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 67, 1, 0, 160, 286, 0, 0, 108, 1, 1.5, 1, 3, 2, 1, 1, 'high', 0.92, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 37, 1, 2, 130, 250, 0, 1, 187, 0, 3.5, 0, 0, 2, 0, 0, 'low', 0.78, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 41, 0, 1, 130, 204, 0, 0, 172, 0, 1.4, 2, 0, 2, 0, 0, 'low', 0.85, CURRENT_TIMESTAMP);

-- Insert sample activity logs
INSERT INTO user_activity_log (user_id, activity_type, description) VALUES
(1, 'login', 'Admin logged in'),
(1, 'prediction', 'Made a new prediction'),
(2, 'login', 'Expert logged in'),
(3, 'prediction', 'Made a new prediction');

-- Update user statistics
UPDATE users 
SET total_predictions = (
    SELECT COUNT(*) 
    FROM predictions 
    WHERE predictions.user_id = users.id
),
prediction_accuracy = (
    SELECT (COUNT(CASE WHEN predicted_outcome = actual_outcome THEN 1 END) / COUNT(*)) * 100
    FROM predictions 
    WHERE predictions.user_id = users.id
)
WHERE id IN (SELECT DISTINCT user_id FROM predictions); 