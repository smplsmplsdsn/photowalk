<?php
header('Content-Type: application/json; charset=utf-8');
include_once(__DIR__ . '/../../../functions/init.php');

// ガード（トークンチェック）
if (
  !isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  echo json_encode([
    'status' => 'fail',
    'message' => 'token error'
  ]);
  exit;
}

$public_id = $_POST['public_id'] ?? null;
$event_id = $_POST['event_id'] ?? null;
$error_message = '<span class="ja">投票できるIDではありません。<br>ID名を確かめてください。</span><span class="en">This ID is not valid for voting in this event.<br>Please double-check your ID name.</span>';


// ガード
if (!$public_id || !$event_id) {
  echo json_encode([
    'status' => 'error',
    'message' => $error_message
  ]);
  exit;
}

try {

  // 1. ユーザー情報取得
  $stmt = $pdo->prepare("
    SELECT id, public_id, handle, display_name, email
    FROM users
    WHERE public_id = :public_id
    LIMIT 1
  ");
  $stmt->execute([':public_id' => $public_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    echo json_encode([
      'status' => 'fail',
      'message' => $error_message
    ]);
    exit;
  }

  $user_id = (int)$user['id'];

  // 2. いいね情報取得
  $stmt = $pdo->prepare("
    SELECT filename, photowalker
    FROM likes
    WHERE user_id = :user_id
      AND event_id = :event_id
  ");
  $stmt->execute([
    ':user_id' => $user_id,
    ':event_id' => $event_id
  ]);
  $likes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo json_encode([
    'status' => 'fail',
    'message' => 'SYSTEM ERROR',
    'message_system' => $e->getMessage()
  ]);
  exit;
}

echo json_encode([
  'status' => 'success',
  'user' => $user,
  'likes' => $likes
]);
