<?php
header('Content-Type: application/json; charset=utf-8');
include_once(__DIR__ . '/../../functions/init.php');

// ガード（トークンチェック）
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
  echo json_encode([
    'status' => 'fail',
    'message' => 'token error'
  ]);
  exit;
}

// 必須パラメータ
$event_name = $_POST['event_name'] ?? null;
$uid = $_POST['uid'] ?? null;
$photowalker = $_POST['photowalker'] ?? null;
$images = $_POST['images'] ?? null;

// ガード
if (!$event_name || !$uid || !$photowalker || !$images) {
  echo json_encode([
    'status' => 'fail',
    'message' => 'Missing required parameters'
  ]);
  exit;
}

try {
  $pdo->beginTransaction();

  // 1. 既存のいいねを削除
  $stmt = $pdo->prepare("
    DELETE FROM likes
    WHERE event_name = :event_name
      AND uid = :uid
      AND photowalker = :photowalker
  ");
  $stmt->execute([
    ':event_name' => $event_name,
    ':uid' => $uid,
    ':photowalker' => $photowalker
  ]);

  // 2. 新しいいいねをINSERT
  $stmt = $pdo->prepare("
    INSERT INTO likes (event_name, uid, photowalker, filename)
    VALUES (:event_name, :uid, :photowalker, :filename)
  ");

  foreach ($images as $filename) {
    $stmt->execute([
      ':event_name' => $event_name,
      ':uid' => $uid,
      ':photowalker' => $photowalker,
      ':filename' => $filename
    ]);
  }

  $pdo->commit();
} catch (PDOException $e) {
  echo json_encode([
    'status' => 'fail'
  ]);
  exit;
}

echo json_encode([
  'status' => 'success'
]);
exit;
