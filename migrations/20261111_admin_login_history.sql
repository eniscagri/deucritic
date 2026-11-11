CREATE TABLE IF NOT EXISTS user_ip_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(512) NOT NULL DEFAULT '',
    country VARCHAR(100) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    isp VARCHAR(190) DEFAULT NULL,
    is_vpn TINYINT(1) NOT NULL DEFAULT 0,
    logged_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_ip_logs_user_date (user_id, logged_at),
    KEY idx_user_ip_logs_ip_date (ip, logged_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
