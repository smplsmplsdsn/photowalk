<?php
header('Content-Type: application/json; charset=utf-8');

$result = [
    'imagick_installed' => extension_loaded('imagick'),
    'imagick_formats' => [],
    'gd_installed' => extension_loaded('gd'),
    'gd_supports' => []
];

// Imagickがあればサポートフォーマット確認
if ($result['imagick_installed']) {
    $imagick = new Imagick();
    $formats = $imagick->queryFormats();
    $result['imagick_formats'] = $formats;
    unset($imagick);
}

// GDがあれば対応形式を確認
if ($result['gd_installed']) {
    $gd_info = gd_info();
    $result['gd_supports'] = [
        'jpeg' => $gd_info['JPEG Support'] ?? false,
        'png'  => $gd_info['PNG Support'] ?? false,
        'webp' => $gd_info['WebP Support'] ?? false
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);