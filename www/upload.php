<?php
session_start();

$maxSize = 10 * 1024 * 1024;
$uploadDir = __DIR__ . '/../storage/photos/260215-koenji-selected/';

// ガード: CSRF検証
// NOTICE: 画像アップロード時の並列処理を有効にするため、session_write_close() している
if (
	!isset($_POST['csrf_token']) ||
	!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
	http_response_code(403);
	exit;
}
session_write_close();

// ガード: method確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit;
}

// ガード: ファイル存在チェック
if (!isset($_FILES['image'])) {
	http_response_code(400);
	exit;
}

// ガード: アップロードエラーチェック（超重要）
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
	http_response_code(400);
	exit;
}

// ガード: MIMEを信用しない
// NOTICE: 拡張子は信用しない
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['image']['tmp_name']);

$allowed = [
	'image/jpeg' => 'jpg',
	'image/png'  => 'png',
	'image/webp' => 'webp',
	'image/heic' => 'heic',
	'image/heif' => 'heif'
];

if (!array_key_exists($mime, $allowed)) {
	http_response_code(400);
	exit('Invalid type');
}

// ガード: サイズチェック
if ($_FILES['image']['size'] > $maxSize) {
	http_response_code(400);
	exit('Too large');
}

if (!is_dir($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}

// 保存ファイル名
// NOTICE: ユーザー名は絶対使わない。
$filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
  http_response_code(500);
  exit;
}

// ここから圧縮処理
echo json_encode([
  'status' => 'ok',
  'filename' => $filename
]);