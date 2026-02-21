<?php
include_once(__DIR__ . '/functions/init.php');
ini_set('display_errors', $is_https ? 0 : 1);

$event_name = $_GET['event_name'] ?? '';

// ガード
if ($event_name === '') {
  exit('NO DATA');
}

switch ($event_name) {
  case '260215-koenji-1':
    exit('Voting in progress');
}


$sql = "
  SELECT
    photowalker,
    filename,
    COUNT(*) AS like_count
  FROM likes
  WHERE event_name = :event_name
  GROUP BY photowalker, filename
  ORDER BY photowalker ASC, like_count DESC
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':event_name', $event_name, PDO::PARAM_STR);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
  exit('NO DATA');
}

/*
 * photowalkerごとに配列を再構築
 */
$grouped = [];

foreach ($results as $row) {
  $photowalker = $row['photowalker'];

  if (!isset($grouped[$photowalker])) {
    $grouped[$photowalker] = [
      'total_like' => 0,
      'items' => []
    ];
  }

  $grouped[$photowalker]['total_like'] += (int) $row['like_count'];
  $grouped[$photowalker]['items'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>自薦＆他薦で決める一枚 集計</title>
  <meta name="description" content="">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap">
  <link rel="stylesheet" href="/assets/css/common.min.css?<?php echo filemtime('./assets/css/common.min.css'); ?>">
  <style>
    body {
      padding: 30px;
    }

    h1 {
      margin: 0 0 20px;
      font-size: 20px;
    }

    th,
    td {
      padding: 10px;
      vertical-align: middle;
    }

    img {
      width: 160px;
      height: 160px;
      object-fit: contain;
      background: #eee;
    }
  </style>
</head>
<body data-lang="ja">
  <h1><?= h($event_name) ?></h1>
  <?php
    uasort($grouped, fn($a, $b) => $b['total_like'] <=> $a['total_like']);
    foreach ($grouped as $photowalker => $data):
  ?>
  <section>
    <h2><?= h($photowalker) ?> (<?= count($data['items']) ?>種、<?= $data['total_like'] ?> likes)</h2>
    <table>
      <?php foreach ($data['items'] as $item): ?>
        <tr>
          <th><?= $item['like_count'] ?></th>
          <td>
            <img src="/assets/photo.php?filename=<?= h($event_name) ?>/<?= h($photowalker) ?>/<?= h($item['filename']) ?>" loading="lazy">
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </section>
  <?php endforeach; ?>
  <script src="/assets/js/jquery-4.0.0.min.js"></script>
  <script src="/assets/js/common.min.js?<?php echo filemtime('./assets/js/common.min.js'); ?>"></script>
</body>
</html>
