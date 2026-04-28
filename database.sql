-- AuditAI Database Schema

CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    website VARCHAR(255),
    contact_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    status ENUM('Pending', 'Preparing', 'Ready', 'Contacted') DEFAULT 'Pending',
    score INT DEFAULT 0,
    audit_engine VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Default Settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('daily_limit', '500'),
('delay_between_messages', '45'),
('email_notifications', '1'),
('whatsapp_notifications', '1');
