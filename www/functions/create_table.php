<?php
$sql = "
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uid VARCHAR(50) NOT NULL,
  handle VARCHAR(50) NOT NULL,
  display_name VARCHAR(100) NULL,
  email VARCHAR(255) NULL,
  password VARCHAR(255) NULL,
  oauth_provider VARCHAR(50) NULL,
  oauth_id VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_uid (uid),
  UNIQUE KEY unique_handle (handle),
  UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
  $pdo->exec($sql);
} catch (PDOException $e) {
  echo "テーブル作成エラー： users";
  exit;
}

$sql = "
CREATE TABLE IF NOT EXISTS likes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id VARCHAR(100) NOT NULL,
  uid VARCHAR(50) NOT NULL,
  photowalker VARCHAR(50) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_like (event_id, uid, filename),
  INDEX idx_event (event_id),
  INDEX idx_uid (uid),
  INDEX idx_photowalker (photowalker),
  INDEX idx_filename (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
  $pdo->exec($sql);
} catch (PDOException $e) {
  echo "テーブル作成エラー： likes";
  exit;
}

$sql = "
CREATE TABLE IF NOT EXISTS event_info (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id VARCHAR(255) NOT NULL,
  title_ja VARCHAR(255) NOT NULL,
  title_en VARCHAR(255) NULL,
  excerpt_ja TEXT NULL,
  excerpt_en TEXT NULL,
  event_date DATE NOT NULL,
  vote_counting_at DATETIME NOT NULL,
  status TINYINT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
  $pdo->exec($sql);
} catch (PDOException $e) {
  echo "テーブル作成エラー： event_info";
  exit;
}

