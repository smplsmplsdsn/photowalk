<?php
$sql = "
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  public_id VARCHAR(50) NOT NULL,
  handle VARCHAR(50) NOT NULL,
  display_name VARCHAR(100) NULL,
  email VARCHAR(255) NULL,
  password VARCHAR(255) NULL,
  oauth_provider VARCHAR(50) NULL,
  oauth_id VARCHAR(255) NULL,
  created_at DATETIME
    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME
    NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_public_id (public_id),
  UNIQUE KEY uq_users_handle (handle),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
  $pdo->exec($sql);
} catch (PDOException $e) {
  echo "テーブル作成エラー： users";
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
  created_at DATETIME
    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME
    NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
  $pdo->exec($sql);
} catch (PDOException $e) {
  echo "テーブル作成エラー： event_info";
  exit;
}


$sql = "
CREATE TABLE IF NOT EXISTS images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  category VARCHAR(100) NULL,
  filename VARCHAR(255) NOT NULL,
  original_filename VARCHAR(255) NULL,
  mime_type VARCHAR(100) NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  file_hash CHAR(64) NOT NULL,
  visibility ENUM('public','private','unlisted')
    NOT NULL DEFAULT 'public',
  shot_at DATETIME NOT NULL,
  created_at DATETIME
    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME
    NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_images_user_hash (user_id, file_hash),
  INDEX idx_images_user_id (user_id),
  INDEX idx_images_user_category (user_id, category),
  INDEX idx_images_user_visibility (user_id, visibility),
  INDEX idx_images_user_shot_at (user_id, shot_at),

  CONSTRAINT fk_images_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
  $pdo->exec($sql);
} catch (PDOException $e) {
  echo "テーブル作成エラー： images";
  exit;
}

$sql = "
CREATE TABLE IF NOT EXISTS likes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id VARCHAR(100) NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  photowalker VARCHAR(50) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  created_at DATETIME
    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_likes_event_user_file (event_id, user_id, filename),
  INDEX idx_likes_event (event_id),
  INDEX idx_likes_user (user_id),
  INDEX idx_likes_photowalker (photowalker),
  INDEX idx_likes_filename (filename),
  CONSTRAINT fk_likes_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
  $pdo->exec($sql);
} catch (PDOException $e) {
  echo "テーブル作成エラー： likes";
  echo $e->getMessage();
  exit;
}
