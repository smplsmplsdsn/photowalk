<?php
include_once(__DIR__ . '/../functions/init.php');
ini_set('display_errors', $is_https ? 0 : 1);

// TODO ログイン成功したとして
// NOTICE: category は POSTで設定する（ユーザー側で変更できないように、constで定義する）
// session_regenerate_id(true);
$_SESSION['upload_dir'] = __DIR__ . '/../storage/photos';
$_SESSION['public_id'] = 'login_test';
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if ($is_https) {
  exit('NOW CLOSED.');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <meta name="description" content="">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap">
  <link rel="stylesheet" href="/assets/css/common.min.css?<?php echo filemtime('./assets/css/common.min.css'); ?>">
  <style>
    a {
      color: #111;
      text-decoration: underline;
      text-decoration-thickness: 1px;
      text-underline-offset: 4px;
    }
  </style>
</head>
<body>

  <div class="uploader">
    <div class="uploader-function">
      <div class="uploader-droparea js-uploader-droparea">
        <input type="file" name="image" class="js-uploader-input" accept="image/*" multiple hidden>
        <div class="uploader-dropinner">画像をドラッグ＆ドロップ<br>または<br>クリックして選択</div>
      </div>
      <div class="js-uploader-filelist"></div>
      <div class="uploader-controls">
        <button type="button" class="js-uploader-button" disabled>アップロード</button>
      </div>
      <ul class="uploader-lists js-uploaded-list">
        <li><img src="./assets/images/checkmark.svg"></li>
        <li><img src="./assets/images/checkmark.svg"></li>
        <li><img src="./assets/images/checkmark.svg"></li>
        <li><img src="./assets/images/checkmark.svg"></li>
        <li><img src="./assets/images/checkmark.svg"></li>
      </ul>
    </div>
    <div class="uploader-list">
      <ul class="uploader-lists"></ul>

    </div>
  </div>



  <script src="/assets/js/jquery-4.0.0.min.js"></script>
  <script>
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?>'
    const CATEGORY = 'koenji3'
  </script>
  <script src="/assets/js/common.min.js?<?php echo filemtime('./assets/js/common.min.js'); ?>"></script>
  <script>
    Fn.uploader()
  </script>
</body>
</html>
