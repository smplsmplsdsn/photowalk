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

// ガード（イベントネーム:スラッグチェック）
$event_id = trim($_POST['event_id'] ?? '');

if ($event_id === '') {
  echo json_encode([
    'status' => 'error',
    'message' => '<span class="ja">投票できるイベントIDではありません。<br>ID名を確かめてください。</span><span class="en">This ID is not valid for voting in this event.<br>Please double-check your ID name.</span>'
  ]);
  exit;
}

// ガード（結果発表日時の妥当性チェック）
$vote_counting_date_at = $_POST['vote_counting_date_at'] ?? null;

if (!$vote_counting_date_at) {
  echo json_encode([
    'status' => 'error',
    'message' => '<span class="ja">結果発表日を入力してください。</span><span class="en">Please enter the results announcement date.</span>'
  ]);
  exit;
}

$date_obj = DateTime::createFromFormat('Y-m-d', $vote_counting_date_at);

if (
  !$date_obj ||
  $date_obj->format('Y-m-d') !== $vote_counting_date_at
) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Invalid date format.'
  ]);
  exit;
}

$vote_counting_time_at = $_POST['vote_counting_time_at'] ?? '00:00';

if (!preg_match('/^\d{2}:\d{2}$/', $vote_counting_time_at)) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Invalid time format.'
  ]);
  exit;
}

$vote_counting_at_string = $vote_counting_date_at . ' ' . $vote_counting_time_at . ':00';

$datetime_obj = DateTime::createFromFormat('Y-m-d H:i:s', $vote_counting_at_string);

if (
  !$datetime_obj ||
  $datetime_obj->format('Y-m-d H:i:s') !== $vote_counting_at_string
) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Invalid datetime value.'
  ]);
  exit;
}

$vote_counting_at = $datetime_obj->format('Y-m-d H:i:s');

try {
  $sql = "
    INSERT INTO event_info (
      event_id,
      title_ja,
      title_en,
      excerpt_ja,
      excerpt_en,
      event_date,
      vote_counting_at,
      status,
      created_at,
      updated_at
    ) VALUES (
      :event_id,
      :title_ja,
      :title_en,
      :excerpt_ja,
      :excerpt_en,
      :event_date,
      :vote_counting_at,
      :status,
      NOW(),
      NOW()
    )
  ";

  $create = $_POST['create'] ?? 'new';

  if ($create === 'update') {
    $sql .= "
      ON DUPLICATE KEY UPDATE
        title_ja = VALUES(title_ja),
        title_en = VALUES(title_en),
        excerpt_ja = VALUES(excerpt_ja),
        excerpt_en = VALUES(excerpt_en),
        event_date = VALUES(event_date),
        vote_counting_at = VALUES(vote_counting_at),
        status = VALUES(status),
        updated_at = NOW()
    ";
  }

  $stmt = $pdo->prepare($sql);

  $stmt->execute([
    ':event_id' => $event_id,
    ':title_ja' => $_POST['title_ja'] ?? 'No Title',
    ':title_en' => $_POST['title_en'] ?? 'No Title',
    ':excerpt_ja' => $_POST['excerpt_ja'] ?? '',
    ':excerpt_en' => $_POST['excerpt_en'] ?? '',
    ':event_date' => $_POST['event_date'] ?? '',
    ':vote_counting_at' => $vote_counting_at,
    ':status' => (int)($_POST['status'] ?? 1),
  ]);
} catch (PDOException $e) {

  $message = ($e->getCode() == 23000) ? '<span class="ja">すでに使用されているイベントIDです</span><span class="en">Event ID already exists.</span>' :  'SYSTEM ERROR';

  echo json_encode([
    'status' => 'fail',
    'message' => $message,
    'meesage_system' => $e->getMessage()
  ]);
  exit;
}

echo json_encode([
  'status' => 'success',
  'message' => '<span class="ja">保存しました。</span><span class="en">Saved</span>'
]);
