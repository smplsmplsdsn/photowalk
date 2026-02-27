<?php
function uploaderProcessImageWithFallback(array $file, array $allowed = [], int $maxSide = 0, int $maxSize = 0) {

  if ($file['error'] !== UPLOAD_ERR_OK) {
    return null;
  }

  if (empty($allowed)) {
    $allowed = [
      'image/jpeg' => 'jpg',
      'image/png'  => 'png',
      'image/webp' => 'webp',
      'image/heic' => 'heic',
      'image/heif' => 'heif'
    ];
  }

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);

  if (!array_key_exists($mime, $allowed)) {
    return null;
  }

  // NOTE: Imagick優先
  if (extension_loaded('imagick')) {
    try {
      $img = new Imagick($file['tmp_name']);
      $img->autoOrient();
      $img->stripImage();

      if ($mime === 'image/heic' || $mime === 'image/heif') {
        $img->setImageBackgroundColor('white');
        $img = $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        $img->setImageFormat('jpeg');
        $img->setImageCompressionQuality(100);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $blob = $img->getImagesBlob();

        if ($finfo->buffer($blob) === 'image/jpeg') {
          $mime = 'image/jpeg';
        }
      }

      if ($maxSide > 0) {
        $width  = $img->getImageWidth();
        $height = $img->getImageHeight();
        $scale  = min($maxSide / $width, $maxSide / $height, 1);

        if ($scale < 1) {
          $img->resizeImage((int)($width * $scale), (int)($height * $scale), Imagick::FILTER_LANCZOS, 1);
        }
      }

      $blob = $img->getImagesBlob();

      if ($maxSize > 0 && ($mime === 'image/jpeg' || $mime === 'image/webp')) {
        $minQ = 10;
        $maxQ = 100;
        $bestBlob = $blob;

        while ($minQ <= $maxQ) {
          $midQ = (int)(($minQ + $maxQ) / 2);
          $img->setImageCompressionQuality($midQ);
          $testBlob = $img->getImagesBlob();
          $len = strlen($testBlob);

          if ($len > $maxSize) {
            $maxQ = $midQ - 1; // サイズ超過 → 画質下げる
          } else {
            $bestBlob = $testBlob; // 規定内 → 保存
            $minQ = $midQ + 1;     // さらに画質上げてみる
          }
        }

        $blob = $bestBlob;
      }

      return ['blob' => $blob, 'mime' => $mime];

    } catch (Exception $e) {

      // Imagickで失敗してもGDでフォールバック
    }
  }

  // NOTE: GD（Imagickのフォールバック）
  if (function_exists('imagecreatefromstring')) {
    $img = @imagecreatefromstring(file_get_contents($file['tmp_name']));

    if (!$img) {
      return null;
    }

    $width  = imagesx($img);
    $height = imagesy($img);

    if ($maxSide > 0) {
      $scale = min($maxSide / $width, $maxSide / $height, 1);

      if ($scale < 1) {
        $newW = (int)($width * $scale);
        $newH = (int)($height * $scale);
        $tmp = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($img);
        $img = $tmp;
      }
    }

    $blob = null;

    if ($maxSize > 0 && ($mime === 'image/jpeg' || $mime === 'image/webp')) {
      $minQ = 10;
      $maxQ = 100;
      $bestBlob = null;

      while ($minQ <= $maxQ) {
        $midQ = (int)(($minQ + $maxQ) / 2);
        ob_start();

        if ($mime === 'image/jpeg') {
          imagejpeg($img, null, $midQ);
        } else {
          imagewebp($img, null, $midQ);
        }

        $testBlob = ob_get_clean();

        $len = strlen($testBlob);

        if ($len > $maxSize) {
          $maxQ = $midQ - 1;
        } else {
          $bestBlob = $testBlob;
          $minQ = $midQ + 1;
        }
      }

      $blob = $bestBlob ?? $blob;
    } else {
      ob_start();
      switch ($mime) {
        case 'image/jpeg': imagejpeg($img, null, 100); break;
        case 'image/png':  imagepng($img); break;
        case 'image/webp': imagewebp($img, null, 100); break;
      }
      $blob = ob_get_clean();
    }

    imagedestroy($img);
    return ['blob' => $blob, 'mime' => $mime];
  }

  return null;
}