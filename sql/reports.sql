CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    type ENUM('prediction_summary', 'user_activity', 'dataset_analysis', 'risk_analysis', 'custom') NOT NULL,
    format ENUM('pdf', 'excel', 'csv', 'json') NOT NULL,
    date_range VARCHAR(50),
    start_date DATE,
    end_date DATE,
    include_charts BOOLEAN DEFAULT FALSE,
    include_raw_data BOOLEAN DEFAULT FALSE,
    file_path VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 