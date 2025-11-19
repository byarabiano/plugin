-- tb_migrations.sql
-- مثال بسيط لإنشاء علاقة بين الكورس والتصنيف الفرعي
-- تأكد من أن prefix الجداول في موقعك هو wp_ أو استخدم القيمة الصحيحة
CREATE TABLE IF NOT EXISTS `wp_tb_course_assignments` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` BIGINT(20) UNSIGNED NOT NULL,
  `subcategory_id` BIGINT(20) UNSIGNED NOT NULL,
  `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_idx` (`course_id`),
  KEY `subcat_idx` (`subcategory_id`),
  -- IF your MySQL version and hosting supports foreign keys, uncomment below
  -- CONSTRAINT `fk_course` FOREIGN KEY (`course_id`) REFERENCES `wp_posts`(`ID`) ON DELETE CASCADE,
  -- CONSTRAINT `fk_subcat` FOREIGN KEY (`subcategory_id`) REFERENCES `tb_subcategory`(`term_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;