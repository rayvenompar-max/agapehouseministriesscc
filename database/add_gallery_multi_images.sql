-- ================================================================
-- Gallery Multi-Image Support Migration
-- Allows multiple images per gallery post for collage display
-- Run once: mysql -u root daybreak < add_gallery_multi_images.sql
-- ================================================================

USE daybreak;

-- Create gallery_images table to store multiple images per gallery post
CREATE TABLE IF NOT EXISTS gallery_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  gallery_id INT UNSIGNED NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  display_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_gallery_id (gallery_id),
  INDEX idx_display_order (display_order),
  
  FOREIGN KEY (gallery_id) REFERENCES gallery(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing single images to gallery_images table
INSERT INTO gallery_images (gallery_id, image_url, display_order)
SELECT id, image_url, 0
FROM gallery
WHERE image_url IS NOT NULL AND image_url != '';

-- Note: We keep the image_url column in gallery table for backward compatibility
-- and as a quick reference to the primary/first image
