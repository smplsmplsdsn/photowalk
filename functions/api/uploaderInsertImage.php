<?php
function uploaderInsertImage(PDO $pdo, array $data): void {
  $stmt = $pdo->prepare("
    INSERT INTO images (
      user_id,
      category,
      filename,
      original_filename,
      mime_type,
      file_size,
      file_hash,
      visibility,
      shot_at,
      created_at,
      updated_at
    ) VALUES (
      :user_id,
      :category,
      :filename,
      :original_filename,
      :mime_type,
      :file_size,
      :file_hash,
      :visibility,
      :shot_at,
      NOW(),
      NOW()
    )
  ");

  try {
    $stmt->execute($data);
  } catch (PDOException $e) {

    if ($e->errorInfo[1] === 1062) {
      throw new RuntimeException('ALREADY_SAVED_IMAGE');
    }
    throw new RuntimeException('DATABASE_SAVE_ERROR' . $e->getMessage());
  }
}