<?php
session_start();

// ガード：直URL対策（偽装できるので、簡易版として）
if (empty($_SERVER['HTTP_REFERER'])) {
  http_response_code(403);
  exit;
}

// ガード：セッション対応
if (empty($_SESSION['csrf_token'])) {
  http_response_code(403);
  exit;
}

$file = $_GET['filename'] ?? '';
$baseDir = realpath(__DIR__ . '/../../storage/photos');
$path = realpath($baseDir . '/' . $file);

// パストラバーサル防止
if (!$path || strpos($path, $baseDir) !== 0) {
  http_response_code(403);
  exit;
}

if (!is_file($path)) {
  http_response_code(404);
  exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $path);

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));

readfile($path);
exit;
