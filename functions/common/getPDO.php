<?php
function getPDO(): PDO {
  date_default_timezone_set('Asia/Tokyo');

  if (!is_file(__DIR__ . '/../config.php')) {
    throw new RuntimeException('DATABASE_SETTING_ERROR');
  }

  require_once __DIR__ . '/../config.php';

  try {
    $pdo = new PDO(
      "mysql:host=$host;dbname=$db;charset=utf8mb4",
      $user,
      $pass,
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
      ]
    );

    return $pdo;

  } catch (PDOException $e) {
    throw new RuntimeException('DATABASE_ERROR');
  }
}