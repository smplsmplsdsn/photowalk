<?php
session_start();

// ガード
if (!is_file(__DIR__ . '/config.php')) {
  header('Location: /setup.php');
  exit;
}

include_once(__DIR__ . '/config.php');

// データベースに接続する
try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo 'SYSTEM ERROR';
  exit();
}

// サーバーサイドを日本時間にする
date_default_timezone_set('Asia/Tokyo');

// お手製のPHPファイルを読み込む
foreach (glob(__DIR__ . '/original/{*.php}', GLOB_BRACE) as $file) {
  if (is_file($file)) {
    include_once($file);
  }
}
