<?php
function json_error($error_code, $httpStatus = 400) {
	http_response_code($httpStatus);
	echo json_encode([
		'status' => 'error',
		'code'   => $error_code
	]);
	exit;
}

$upload_dir = $_SESSION['upload_dir'];
$max_size = intval(0.6 * 1024 * 1024);
$max_side = 1440;
$allowed = [
	'image/jpeg' => 'jpg',
	'image/png'  => 'png',
	'image/webp' => 'webp',
	'image/heic' => 'heic',
	'image/heif' => 'heif'
];
$allowed_visibility = ['public', 'private'];

// ディレクトリ
if (!is_dir($upload_dir)) {
	json_error('NO_DIR');
}

// TODO session id でログイン判定して、public_idも取得しておくこと
if (empty($_SESSION['user_id'])) {
	json_error('UNAUTHORIZED', 401);
}

$user_id = $_SESSION['user_id'] ?? '';
$public_id = $_SESSION['public_id'] ?? '';

$category = $_POST['category'] ?? '';

if ($category !== '' && !preg_match('/^[a-zA-Z0-9_-]+$/', $category)) {
	json_error('INVALID_DIR');
}

// CSRF
if (
	!isset($_POST['csrf_token']) ||
	!isset($_SESSION['csrf_token']) ||
	!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
	json_error('CSRF_INVALID', 403);
}

session_write_close();

// method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	json_error('METHOD_NOT_ALLOWED', 405);
}

// file exists
if (!isset($_FILES['image'])) {
	json_error('NO_FILE');
}

// 元サイズ check（サーバー保護、20MBの設定は適当）
if ($_FILES['image']['size'] > 100 * 1024 * 1024) {
    json_error('FILE_TOO_LARGE');
}

// upload error cehck
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

	switch ($_FILES['image']['error']) {

		// php.ini 制限超え、フォーム指定サイズ超え
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
		case 1:
		case 2:
			json_error('FILE_TOO_LARGE');
			break;

		// 一部しかアップロードされなかった（通信切れなど）
		case UPLOAD_ERR_PARTIAL:
		case 3:
			json_error('TIMEOUT_ERROR');
			break;

		// UPLOAD_ERR_NO_FILE 4:ファイル未選択
		// UPLOAD_ERR_NO_TMP_DIR 6:一時フォルダがない（サーバー設定ミス）

		// 書き込み失敗（権限・容量）
		case UPLOAD_ERR_CANT_WRITE:
		case 7:
			json_error('UPLOAD_ERR_CANT_WRITE');
			break;

		// 拡張による停止（セキュリティ系z）
		case UPLOAD_ERR_EXTENSION:
		case 7:
			json_error('UPLOAD_ERR_EXTENSION');
			break;

		default:
			json_error('UPLOAD_ERROR');
	}
}

// MIME check
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['image']['tmp_name']);

if (!array_key_exists($mime, $allowed)) {
	json_error('INVALID_TYPE');
}

$processed_result = uploaderProcessImageWithFallback($_FILES['image'], $allowed, $max_side, $max_size);

if (!$processed_result) {
	json_error('INVALID_IMAGE', 400);
}

$processed = $processed_result['blob'];
$mime = $processed_result['mime'];
$show_at = $processed_result['show_at'];
$prefix = $show_at->format('YmdHis') . '_';

$upload_dir = $upload_dir . '/' . $public_id;

if (!is_dir($upload_dir)) {
	mkdir($upload_dir, 0755, true);
}

if ($category != '') {
	$upload_dir = $upload_dir . '/' . $category;

	if (!is_dir($upload_dir)) {
		mkdir($upload_dir, 0755, true);
	}
}

do {
	$filename = $prefix . bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
	$target_path = $upload_dir . '/' . $filename;
} while (file_exists($target_path));

try {
  $pdo = getPDO();
  $pdo->beginTransaction();

  if (file_put_contents($target_path, $processed) === false) {
    throw new RuntimeException('SAVE_FAILED');
  }

	$visibility = $_POST['visibility'] ?? 'public';

	if (!in_array($visibility, $allowed_visibility, true)) {
		$visibility = 'public';
	}

	$data = [
		'user_id' => $user_id,
		'category' => $category,
		'filename' => $filename,
		'original_filename' => trim(basename($_FILES['image']['name'])),
		'mime_type' => $mime,
		'file_size' => strlen($processed),
		'file_hash' => hash('sha256', $processed),
		'visibility' => $visibility,
		'shot_at' => $show_at->format('Y-m-d H:i:s'),
	];
  uploaderInsertImage($pdo, $data);
  $pdo->commit();
} catch (Throwable $e) {

  if (isset($pdo) && $pdo->inTransaction()) {
    $pdo->rollBack();
  }

  if (!empty($target_path) && is_file($target_path)) {
    unlink($target_path);
  }

  if ($e->getMessage() === 'ALREADY_SAVED_IMAGE') {
    json_error('ALREADY_SAVED_IMAGE', 400);
  }

  json_error('UPLOAD_FAILED' . $e, 500);
}

echo json_encode([
	'status' => 'success',
	'public_id' => $public_id,
	'filename' => $filename
]);