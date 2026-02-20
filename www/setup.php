<?php
function h($value) {
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$config_file = __DIR__ . '/functions/config.php';
$create_table = __DIR__ . '/functions/create_table.php';

$is_db_connect = false;
$can_show_error = true;
$is_config_file = false;
$host = $db = $user = $pass = null;


if (
  isset($_POST['host'], $_POST['db'], $_POST['user'], $_POST['pass']) &&
  $_POST['host'] !== '' &&
  $_POST['db'] !== '' &&
  $_POST['user'] !== ''
) {
  $host = $_POST['host'];
  $db   = $_POST['db'];
  $user = $_POST['user'];
  $pass = $_POST['pass'];
} else if (is_file($config_file)) {
  $is_config_file = true;
  include_once($config_file);
} else {
  $can_show_error = false;
}

if ($host && $db && $user !== null) {

  try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // config生成
    if (!$is_config_file) {
      $config_array = [
        'host' => $host,
        'db'   => $db,
        'user' => $user,
        'pass' => $pass,
      ];

      $config_data = "<?php\n";

      foreach ($config_array as $key => $value) {
        $config_data .= "\$$key = " . var_export($value, true) . ";\n";
      }

      file_put_contents($config_file, $config_data);
    }

    if (is_file($create_table)) {
      include_once($create_table);
      $is_db_connect = true;

      header('Location: /');
      exit;
    }
  } catch (PDOException $e) {

    if (is_file($config_file)) {
      unlink($config_file);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <meta name="description" content="">
  <link rel="stylesheet" href="/assets/css/common.min.css?<?php echo filemtime('./assets/css/common.min.css'); ?>">
  <style>
    form {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    h1 {
      margin: 0 0 10px;
      padding: 0;
      font-size: 16px;
      line-height: 1.5;
      text-align: center;
    }
    h1 .ja {
      display: block;
      font-size: 20px;
    }
    ul {
      margin: 0 0 10px;
      padding: 0;
      list-style: none;
    }
    li + li {
      margin: 5px 0 0;
    }
    div {
      display: flex;
      justify-content: center;
    }
    p {
      margin: 10px 0 0;
      color: #c00;
      font-weight: bold;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="flex-center">
    <form class="js-form-setup" method="post" action="./setup.php">
      <h1>
        <span class="ja">データベース接続確認</span>
        <span class="en">Database Connection Check</span>
      </h1>
      <ul>
        <li>
          <input type="text" name="host" value="<?= h($host); ?>" placeholder="hostname">
        </li>
        <li>
          <input type="text" name="db" value="<?= h($db); ?>" placeholder="database">
        </li>
        <li>
          <input type="text" name="user" value="<?= h($user); ?>" placeholder="user">
        </li>
        <li>
          <input type="text" name="pass" value="<?= h($pass); ?>" placeholder="password">
        </li>
      </ul>
      <div>
        <button type="submit">
          <span class="ja">チェック</span>
          <span class="en" style="display:none;">Check</span>
        </button>
      </div>
      <?php if (!$is_db_connect && $can_show_error): ?>
        <p>接続できませんでした。</p>
      <?php endif; ?>
    </form>
  </div>
</body>
</html>
