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
$event_id = $_POST['event_id'] ?? null;
$uid = $_POST['uid'] ?? null;
$photowalker = $_POST['photowalker'] ?? null;
$images = $_POST['images'] ?? null;

// ガード
if (!$event_id || !$uid || !$photowalker || !$images) {
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
    WHERE event_id = :event_id
      AND uid = :uid
      AND photowalker = :photowalker
  ");
  $stmt->execute([
    ':event_id' => $event_id,
    ':uid' => $uid,
    ':photowalker' => $photowalker
  ]);

  // 2. 新しいいいねをINSERT
  $stmt = $pdo->prepare("
    INSERT INTO likes (event_id, uid, photowalker, filename)
    VALUES (:event_id, :uid, :photowalker, :filename)
  ");

  foreach ($images as $filename) {
    $stmt->execute([
      ':event_id' => $event_id,
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
