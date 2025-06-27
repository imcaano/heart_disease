-- Create database if not exists
CREATE DATABASE IF NOT EXISTS heart_disease;
USE heart_disease;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    wallet_address VARCHAR(42) UNIQUE,
    role ENUM('user', 'admin', 'expert', 'developer') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Predictions table
CREATE TABLE IF NOT EXISTS predictions (
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
    actual_outcome TINYINT,
    prediction_result ENUM('high', 'medium', 'low') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- User activity log table
CREATE TABLE IF NOT EXISTS user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user if not exists
INSERT IGNORE INTO users (username, password, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@admin.com', 'admin'); 