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

// ガード
if (!$event_id) {
  echo json_encode([
    'status' => 'error',
    'message' => '<span class="ja">投票できるイベントIDではありません。<br>ID名を確かめてください。</span><span class="en">This ID is not valid for voting in this event.<br>Please double-check your ID name.</span>'
  ]);
  exit;
}

// ガード（パストラバーサル防止）
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $event_id)) {
  echo json_encode([
    'status' => 'error',
    'message' => '<span class="ja">投票できるイベントIDではありません。<br>ID名を確かめてください。</span><span class="en">This ID is not valid for voting in this event.<br>Please double-check your ID name.</span>'
  ]);
  exit;
}

try {
  $sql = "SELECT title_ja, title_en, excerpt_ja, excerpt_en, event_date, vote_counting_at FROM event_info WHERE event_id = :event_id LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':event_id' => $event_id]);

  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo json_encode([
      'status' => 'fail',
      'message' => '<span class="ja">このイベント情報はありません。</span><span class="en">No information is available for this event.</span>'
    ]);
    exit;
  }


  $base_path = __DIR__ . '/../../../storage/photos/' . $event_id;

  if (!is_dir($base_path)) {
    echo json_encode([
      'status' => 'error',
      'message' => '<span class="ja">投票できる写真はまだありません。</span><span class="en">There are no photos available for voting yet.</span>'
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


  $vote_dt = new DateTime($row['vote_counting_at']);
  $now = new DateTime();

  if ($vote_dt < $now) {
    echo json_encode([
      'status' => 'fail',
      'message' => '
        <span class="ja">このイベントへの投票はすでに終了しています。</span><span class="en">The voting period for this event has ended.</span><br>
        <a href="/report.php?event_id=' . $event_id . '"><span class="ja">結果発表！！！</span><span class="en">The Results Are In!!!</span></a>
      '
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
  'photowalkers' => $photo_walkers,
  'title_ja' => $row['title_ja'],
  'title_en' => $row['title_en'],
  'excerpt_ja' => $row['excerpt_ja'],
  'excerpt_en' => $row['excerpt_en'],
  'date' => strtotime($row['event_date']),
  'vote_counting_at' => strtotime($row['vote_counting_at']),
]);
