<?php
header('Content-Type: application/json; charset=utf-8');
include_once(__DIR__ . '/../../functions/init.php');

do {
  $uid = $handle = generateHandle();

  try {
    $stmt = $pdo->prepare("INSERT INTO users (uid, handle) VALUES (:uid, :handle)");
    $stmt->execute([
      ':uid' => $uid,
      ':handle' => $handle
    ]);
    break;
  } catch (PDOException $e) {

    // 重複なら再生成
    if ($e->errorInfo[1] == 1062) {
      continue;
    }

    echo json_encode([
      'status' => 'fail',
      'message' => $e->getMessage()
    ]);
    exit;
  }
} while(true);

echo json_encode([
  'status' => 'success',
  'uid' => $uid,
]);
exit;
