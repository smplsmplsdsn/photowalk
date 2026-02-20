<?php
include_once(__DIR__ . '/functions/init.php');
ini_set('display_errors', $is_https ? 0 : 1);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
</head>
<body data-lang="ja">
  <div class="js-page"></div>
  <script src="/assets/js/jquery-4.0.0.min.js"></script>
  <script>
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token']; ?>'
  </script>
  <script src="/assets/js/common.min.js?<?php echo filemtime('./assets/js/common.min.js'); ?>"></script>
</body>
</html>
