<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

function json_error($error_code, $httpStatus = 400) {
	http_response_code($httpStatus);
	echo json_encode([
		'status' => 'error',
		'code'   => $error_code
	]);
	exit;
}

$maxSize = 10 * 1024 * 1024;
$uploadDir = __DIR__ . '/../../../storage/photos/testtesttest/';

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

$allowed = [
	'image/jpeg' => 'jpg',
	'image/png'  => 'png',
	'image/webp' => 'webp',
	'image/heic' => 'heic',
	'image/heif' => 'heif'
];

if (!array_key_exists($mime, $allowed)) {
	json_error('INVALID_TYPE');
}

// size
if ($_FILES['image']['size'] > $maxSize) {
	json_error('FILE_TOO_LARGE');
}

if (!is_dir($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}

do {
    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $targetPath = $uploadDir . $filename;
} while (file_exists($targetPath));

if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
	json_error('MOVE_FAILED', 500);
}

// 成功
echo json_encode([
	'status' => 'success',
	'filename' => $filename
]);