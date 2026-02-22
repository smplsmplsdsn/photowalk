<?php
include_once(__DIR__ . '/functions/init.php');
ini_set('display_errors', $is_https ? 0 : 1);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$event_name = $_GET['event_name'] ?? '';

// ガード
if ($event_name === '') {
  $error_message = 'NO DATA';
}

$sql = "SELECT title_ja, title_en, vote_counting_at FROM event_info WHERE event_name = :event_name LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':event_name' => $event_name]);

$result = $stmt->fetch(PDO::FETCH_ASSOC);
$vote_counting_at = '';
$event_name_ja = $event_name;
$event_name_en = $event_name;

if (empty($result)) {
  $error_message = 'NO DATA';
} else {
  $vote_counting_at = $result['vote_counting_at'];
  $vote_dt = new DateTime($vote_counting_at);
  $now = new DateTime();

  if ($vote_dt >= $now) {
    $error_message = '
      <span class="ja">投票受付中！</span>
      <span class="en">Voting in progress!</span>
      <a href="/?event_name=' . $event_name . '">
        <span class="ja">投票する</span>
        <span class="en">Vote</span>
      </a>
    ';
  }

  $event_name_ja = $result['title_ja'];
  $event_name_en = $result['title_en'];

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
    $error_message = 'NO DATA';
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
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Michroma:wght@400;700&display=swap">
  <link rel="stylesheet" href="/assets/css/common.min.css?<?php echo filemtime('./assets/css/common.min.css'); ?>">
  <style>
    .countdown {
      margin: 10px 0 0;
      font-family: 'Michroma', sans-serif;
      font-size: 32px;
      transform: scale(1, 2);
      transform-origin: 0 0;
    }

    body {
      padding: 30px;
    }

    h1 {
      margin: 0 0 20px;
      font-size: 20px;
      line-height: 1.5;
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

    a {
      color: #111;
      text-decoration: underline;
      text-decoration-thickness: 1px;
      text-underline-offset: 4px;
   }
  </style>
</head>
<body data-lang="ja">
  <?php if (isset($error_message)): ?>
    <h1>
      <span class="ja"><?= h($event_name_ja) ?></span>
      <span class="en"><?= h($event_name_en) ?></span>
    </h1>
    <p><?= $error_message ?></p>
    <div class="countdown js-countdown"></div>
  <?php else: ?>
    <h1>
      <span class="ja"><?= h($event_name_ja) ?> 結果発表！</span>
      <span class="en"><?= h($event_name_en) ?> Results Announcement!</span>
    </h1>
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
  <?php endif; ?>
  <script src="/assets/js/jquery-4.0.0.min.js"></script>
  <script>
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?>'
    const PARAM_EVENT_NAME = '<?= $event_name ?>'
  </script>
  <script src="/assets/js/common.min.js?<?php echo filemtime('./assets/js/common.min.js'); ?>"></script>

  <script>
    const end_datetime = '<?= $vote_counting_at ?>'

    $(()=> {
      if ($('.js-countdown').length > 0 && end_datetime != '') {
        const end_timestamp = new Date(end_datetime.replace(' ', 'T')).getTime()

        Fn.doContDown(end_timestamp, $(".js-countdown"), () => {
          const params = new URLSearchParams(window.location.search)

          if (location.href.indexOf('countdown=done') === -1) {
            params.set("countdown", 'done')
            history.replaceState(null, "", "?" + params.toString())
            location.reload();
          }
        })
      }
    })

  </script>
</body>
</html>
