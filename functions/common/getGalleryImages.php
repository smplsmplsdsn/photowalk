<?php
/**
 * jpeg画像の一覧を配列で取得する
 * 対象は、指定フォルダ内のサブフォルダも対象とする
 *
 * $dir 指定フォルダのサーバーフルパス e.g. /var/www/html/assets/gallery/
 * $trim サーバーのフルパスから公開パスを生成するための文字列 e.g /var/www/html をセットした場合の返却は /assets/gallery/
 */
function getGalleryImages($dir, $trim = '') {
  $result = [];

  $iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
  );

  foreach ($iterator as $file) {
    if (!$file->isFile()) continue;

    $ext = strtolower($file->getExtension());

    if (in_array($ext, ['jpg', 'jpeg'])) {
      $fullPath = $file->getPathname();
      $publicPath = str_replace($trim, '', $fullPath);

      $result[] = $publicPath;
    }
  }

  return $result;
}