CREATE DATABASE okulsozluk;
USE okulsozluk;

-- Kullanıcılar
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    school VARCHAR(100),
    grade_level VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Engellenen kullanıcılar
CREATE TABLE user_blocks (
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (blocker_id, blocked_id),
    KEY idx_user_blocks_blocked_id (blocked_id)
);

-- Başlıklar
CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Entryler (Yorumlar)
CREATE TABLE entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT,
    user_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Şikayetler (Moderasyon)
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_id INT,
    reported_by INT,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES entries(id),
    FOREIGN KEY (reported_by) REFERENCES users(id)
);

-- Login IP and device metadata (IP does not provide precise physical location).
CREATE TABLE user_ip_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(512) NOT NULL DEFAULT '',
    country VARCHAR(100) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    isp VARCHAR(190) DEFAULT NULL,
    is_vpn TINYINT(1) NOT NULL DEFAULT 0,
    logged_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user_ip_logs_user_date (user_id, logged_at),
    KEY idx_user_ip_logs_ip_date (ip, logged_at)
);
