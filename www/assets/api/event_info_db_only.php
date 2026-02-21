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

$event_name = $_POST['event_name'] ?? null;

// ガード
if (!$event_name) {
  echo json_encode([
    'status' => 'error',
    'message' => '<span class="ja">投票できるイベントIDではありません。<br>ID名を確かめてください。</span><span class="en">This ID is not valid for voting in this event.<br>Please double-check your ID name.</span>'
  ]);
  exit;
}

try {
  $sql = "SELECT * FROM event_info WHERE event_name = :event_name LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':event_name' => $event_name]);

  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo json_encode([
      'status' => 'fail',
      'message' => '<span class="ja">このイベント情報はありません。</span><span class="en">No information is available for this event.</span>'
    ]);
    exit;
  }
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
  'data' => $row,
]);

