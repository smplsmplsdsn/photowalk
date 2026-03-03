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

$event_id = $_POST['event_id'] ?? null;
$error_message = '<span class="ja">投票できるIDではありません。<br>ID名を確かめてください。</span><span class="en">This ID is not valid for voting in this event.<br>Please double-check your ID name.</span>';

// ガード
if (!$event_id) {
  echo json_encode([
    'status' => 'error',
    'message' => $error_message
  ]);
  exit;
}

try {

  // TODO ここでログイン判定すべきではないかも。
  // ログイン情報取得と、いいね情報取得は別フローであるべきかな。
  // 1. ユーザー情報取得
  $public_id = $_POST['public_id'] ?? null;
  $user_id = $_SESSION['user_id'] ?? null;

  $where = '';
  $params = [];

  if ($user_id) {
    $where = 'id = :user_id';
    $params[':user_id'] = $user_id;
  } elseif ($public_id) {
    $where = 'public_id = :public_id';
    $params[':public_id'] = $public_id;
  } else {
    echo json_encode([
      'status' => 'fail',
      'message' => $error_message
    ]);
    exit;
  }

  $sql = "
    SELECT id, public_id, handle, display_name, email
    FROM users
    WHERE $where
    LIMIT 1
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    $error_message_none_user = (empty($_SESSION['user_id']))
      ? $error_message
      : '';

    unset($_SESSION['user_id']);

    echo json_encode([
      'status' => 'fail',
      'message' => $error_message_none_user
    ]);
    exit;
  }

  $user_id = (int)$user['id'];
  $_SESSION['user_id'] = $user_id;

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
