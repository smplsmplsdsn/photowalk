<?php
header('Content-Type: application/json; charset=utf-8');
include_once(__DIR__ . '/../../functions/init.php');

// ガード（トークンチェック）
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
  echo json_encode([
    'status' => 'fail',
    'message' => 'token ' . $_SESSION['csrf_token'] . ' ' .$_POST['csrf_token']
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

// TODO $event_name をトリガーとした titl, date, excerpt の DB化 開票日も追加して、report.php で活用する
echo json_encode([
  'status' => 'success',
  'photowalkers' => $photo_walkers,
  'title' => '<span class="ja">高円寺フォトウォーキング</span><span class="en">Photowalking in Koenji</span>',
  'date' => 'Feb 15, 2026',
  'excerpt' => '<span class="ja">スピンオフ企画へのご参加ありがとうございます！</span>',
]);
