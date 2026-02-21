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

// ガード（パストラバーサル防止）
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $event_name)) {
  echo json_encode([
    'status' => 'error',
    'message' => '<span class="ja">投票できるイベントIDではありません。<br>ID名を確かめてください。</span><span class="en">This ID is not valid for voting in this event.<br>Please double-check your ID name.</span>'
  ]);
  exit;
}


$base_path = __DIR__ . '/../../../storage/photos/' . $event_name;

if (!is_dir($base_path)) {
  echo json_encode([
    'status' => 'error',
    'message' => '<span class="ja">投票できるイベントIDではありません。<br>ID名を確かめてください。</span><span class="en">This ID is not valid for voting in this event.<br>Please double-check your ID name.</span>'
  ]);
  exit;
}

$photo_walkers = [];

$sub_dirs = scandir($base_path);

foreach ($sub_dirs as $dir) {

  if ($dir === '.' || $dir === '..') {
    continue;
  }

  $full_path = $base_path . '/' . $dir;

  if (is_dir($full_path)) {

    $images = [];

    $files = scandir($full_path);

    foreach ($files as $file) {

      if ($file === '.' || $file === '..') {
        continue;
      }

      $file_path = $full_path . '/' . $file;

      if (is_file($file_path)) {

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg'])) {
          $images[] = $file;
        }
      }
    }

    $photo_walkers[] = [
      'name' => $dir,
      'images' => $images
    ];
  }
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

  $vote_dt = new DateTime($row['vote_counting_at']);
  $now = new DateTime();

  if ($vote_dt < $now) {
    echo json_encode([
      'status' => 'fail',
      'message' => '<span class="ja">このイベントへの投票はすでに終了しています。</span><span class="en">The voting period for this event has ended.</span>'
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

// TODO $event_name をトリガーとした titl, date, excerpt の DB化 開票日も追加して、report.php で活用する
echo json_encode([
  'status' => 'success',
  'photowalkers' => $photo_walkers,
  'title' => '<span class="ja">' . $row['title_ja'] . '</span><span class="en">' . $row['title_en'] . '</span>',
  'date' => date('M d, Y', strtotime($row['event_date'])),
  'excerpt' => '<span class="ja">' . $row['excerpt_ja'] . '</span><span class="en">' . $row['excerpt_en'] . '</span>',
]);
