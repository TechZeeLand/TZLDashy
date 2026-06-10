-- TZLDashy Database Schema
-- Owner: rayaz.org | Creator: TechZeeLand

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS apps;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS two_factor_codes;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================
-- USERS
-- =====================
CREATE TABLE users (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(100) NOT NULL,
  email        VARCHAR(150) NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,
  role         ENUM('admin','user') NOT NULL DEFAULT 'user',
  avatar       VARCHAR(255) DEFAULT NULL,
  totp_secret  VARCHAR(64) DEFAULT NULL,
  totp_enabled TINYINT(1) DEFAULT 0,
  email_2fa    TINYINT(1) DEFAULT 0,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================
-- SESSIONS
-- =====================
CREATE TABLE sessions (
  id         VARCHAR(128) PRIMARY KEY,
  user_id    INT NOT NULL,
  ip         VARCHAR(45),
  user_agent VARCHAR(500),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================
-- CATEGORIES
-- =====================
CREATE TABLE categories (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================
-- APPS
-- =====================
CREATE TABLE apps (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(50) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  image       VARCHAR(255) NOT NULL,
  link        VARCHAR(512) NOT NULL,
  category_id INT DEFAULT NULL,
  sort_order  INT DEFAULT 0,
  location    ENUM('home','apps') NOT NULL DEFAULT 'apps',
  CONSTRAINT fk_category
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================
-- SETTINGS (key-value store per user or global)
-- =====================
CREATE TABLE settings (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT DEFAULT NULL COMMENT 'NULL = global',
  setting_key   VARCHAR(100) NOT NULL,
  setting_value TEXT,
  UNIQUE KEY unique_setting (user_id, setting_key),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================
-- CONTACT MESSAGES
-- =====================
CREATE TABLE contact_messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  subject    VARCHAR(200) NOT NULL,
  message    TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  is_read    TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================
-- TWO FACTOR CODES (email OTP)
-- =====================
CREATE TABLE two_factor_codes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  code       VARCHAR(10) NOT NULL,
  expires_at DATETIME NOT NULL,
  used       TINYINT(1) DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================
-- DEFAULT GLOBAL SETTINGS
-- =====================
INSERT INTO settings (user_id, setting_key, setting_value) VALUES
  (NULL, 'app_name', 'TZLDashy'),
  (NULL, 'theme', 'dark'),
  (NULL, 'accent_color', '#00ffbf'),
  (NULL, 'font', 'Alata'),
  (NULL, 'language', 'en'),
  (NULL, 'setup_done', '0'),
  (NULL, 'weather_city', 'Dhaka');
