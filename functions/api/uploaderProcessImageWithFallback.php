<?php
function uploaderProcessImageWithFallback(array $file, array $allowed = [], int $max_side = 0, int $max_size = 0) {

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

  $server_timezone = new DateTimeZone(date_default_timezone_get());
  $utc_timezone    = new DateTimeZone('UTC');
  $date_now = new DateTime('now', $utc_timezone);

  if (!array_key_exists($mime, $allowed)) {
    return null;
  }

  // NOTE: Imagick優先
  if (extension_loaded('imagick')) {
    try {
      $img = new Imagick();
      $img->setResourceLimit(Imagick::RESOURCETYPE_MEMORY, 256);
      $img->setResourceLimit(Imagick::RESOURCETYPE_MAP, 256);
      $img->readImage($file['tmp_name']);

      $width  = $img->getImageWidth();
      $height = $img->getImageHeight();

      if ($width > 12000 || $height > 12000) {
        $img->clear();
        $img->destroy();
        return null;
      }

      if (($width * $height) > 70_000_000) {
        $img->clear();
        $img->destroy();
        return null;
      }

      $img->autoOrient();

      $icc = $img->getImageProfile('icc');

      $shot_at_raw = null;
      $shot_at = null;
      $exif = $img->getImageProperties('exif:*');

      if (!empty($exif['exif:DateTimeOriginal'])) {
        $shot_at_raw = $exif['exif:DateTimeOriginal'];
      } elseif (!empty($exif['exif:CreateDate'])) {
        $shot_at_raw = $exif['exif:CreateDate'];
      } elseif (!empty($exif['exif:DateTime'])) {
        $shot_at_raw = $exif['exif:DateTime'];
      }

      if ($shot_at_raw) {
        $shot_at = DateTime::createFromFormat(
          'Y:m:d H:i:s',
          $shot_at_raw,
          $server_timezone
        );

        if (!$shot_at) {

          try {
            $shot_at = new DateTime($shot_at_raw, $server_timezone);
          } catch (Exception $e) {
            $shot_at = null;
          }
        }

        if ($shot_at) {
          $shot_at->setTimezone($utc_timezone);
        }
      }

      if (!$shot_at) {
        $shot_at = clone $date_now;
      }

      $img->stripImage();

      if ($icc) {
        $img->setImageProfile('icc', $icc);
      }

      if ($mime === 'image/heic' || $mime === 'image/heif') {
        $img->setImageBackgroundColor('white');
        $tmp = $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        $img->clear();
        $img->destroy();
        $img = $tmp;
        $img->setImageFormat('jpeg');
        $img->setImageCompressionQuality(100);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $blob = $img->getImagesBlob();

        if ($finfo->buffer($blob) === 'image/jpeg') {
          $mime = 'image/jpeg';
        }
      }

      if ($max_side > 0) {
        $width  = $img->getImageWidth();
        $height = $img->getImageHeight();
        $scale  = min($max_side / $width, $max_side / $height, 1);

        if ($scale < 1) {
          $img->resizeImage((int)($width * $scale), (int)($height * $scale), Imagick::FILTER_LANCZOS, 1);
        }
      }

      $blob = $img->getImagesBlob();

      if ($max_size > 0 && ($mime === 'image/jpeg' || $mime === 'image/webp')) {
        $minQ = 10;
        $maxQ = 100;
        $bestBlob = $blob;

        while ($minQ <= $maxQ) {
          $midQ = (int)(($minQ + $maxQ) / 2);
          $img->setImageCompressionQuality($midQ);
          $testBlob = $img->getImagesBlob();
          $len = strlen($testBlob);

          if ($len > $max_size) {
            $maxQ = $midQ - 1; // サイズ超過 → 画質下げる
          } else {
            $bestBlob = $testBlob; // 規定内 → 保存
            $minQ = $midQ + 1;     // さらに画質上げてみる
          }
        }

        $blob = $bestBlob;
      }

      $img->clear();
      $img->destroy();

      return [
        'blob' => $blob,
        'mime' => $mime,
        'show_at' => $shot_at
      ];

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

    if ($width > 12000 || $height > 12000) {
      imagedestroy($img);
      return null;
    }

    if (($width * $height) > 70_000_000) {
      imagedestroy($img);
      return null;
    }

    if ($max_side > 0) {
      $scale = min($max_side / $width, $max_side / $height, 1);

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

    if ($max_size > 0 && ($mime === 'image/jpeg' || $mime === 'image/webp')) {
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

        if ($len > $max_size) {
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

    return [
      'blob' => $blob,
      'mime' => $mime,
      'show_at' => clone $date_now
    ];
  }

  return null;
}