<?php
session_start();

$_SESSION['csrf_token_for_img'] = bin2hex(random_bytes(32));

echo json_encode([
  'status' => 'success',
  'csrf_token' => $_SESSION['csrf_token_for_img']
]);