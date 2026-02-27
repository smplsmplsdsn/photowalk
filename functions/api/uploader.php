<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$upload_dir = $_SESSION['upload_dir'];
$max_size = intval(0.5 * 1024 * 1024);
$max_side = 1350;
$allowed = [
	'image/jpeg' => 'jpg',
	'image/png'  => 'png',
	'image/webp' => 'webp',
	'image/heic' => 'heic',
	'image/heif' => 'heif'
];

function json_error($error_code, $httpStatus = 400) {
	http_response_code($httpStatus);
	echo json_encode([
		'status' => 'error',
		'code'   => $error_code
	]);
	exit;
}

// ログイン判定
if (empty($_SESSION['user_id'])) {
    json_error('UNAUTHORIZED', 401);
}

// ディレクトリ
if (!is_dir($upload_dir)) {
	json_error('NO_DIR');
}

$upload_sub_1 = $_POST['dir1'] ?? '';
$upload_sub_2 = $_SESSION['dir2'] ?? '';

if ($upload_sub_1 !== '' && !preg_match('/^[a-zA-Z0-9_-]+$/', $upload_sub_1)) {
	json_error('INVALID_DIR');
}

if ($upload_sub_2 !== '' && !preg_match('/^[a-zA-Z0-9_-]+$/', $upload_sub_2)) {
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

// upload error
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
	json_error('UPLOAD_ERROR');
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

if ($upload_sub_1 != '') {
	$upload_dir = $upload_dir . '/' . $upload_sub_1;

	if (!is_dir($upload_dir)) {
		mkdir($upload_dir, 0755, true);
	}
}

if ($upload_sub_2 != '') {
	$upload_dir = $upload_dir . '/' . $upload_sub_2;

	if (!is_dir($upload_dir)) {
		mkdir($upload_dir, 0755, true);
	}
}

do {
    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $targetPath = $upload_dir . '/' . $filename;
} while (file_exists($targetPath));


if (file_put_contents($targetPath, $processed) === false) {
	json_error('SAVE_FAILED', 500);
}

echo json_encode([
	'status' => 'success',
	'filename' => $filename
]);