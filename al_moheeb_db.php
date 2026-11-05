<?php
$host = 'localhost:81';
$user = 'root';
$pass = '';

try {
    // الاتصال بدون تحديد قاعدة بيانات
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // كود SQL
    $sql = "
    -- إنشاء قاعدة البيانات
    CREATE DATABASE IF NOT EXISTS `al_moheeb_db` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

    -- استخدام قاعدة البيانات
    USE `al_moheeb_db`;

    -- إنشاء جدول المشاريع مع التصنيفات الجديدة
    CREATE TABLE IF NOT EXISTS `projects` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `description` text DEFAULT NULL,
      `category` enum('tank-insulation','roof-insulation','epoxy','marble') NOT NULL,
      `image_url` varchar(500) NOT NULL,
      `location` varchar(100) DEFAULT NULL,
      `duration` varchar(50) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_category` (`category`),
      KEY `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- إنشاء جدول المستخدمين
    CREATE TABLE IF NOT EXISTS `admin_users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(50) NOT NULL,
      `password_hash` varchar(255) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- إضافة المستخدم الافتراضي
    INSERT IGNORE INTO `admin_users` (`id`, `username`, `password_hash`, `created_at`) VALUES
    (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', CURRENT_TIMESTAMP);
    ";
    
    // تنفيذ الاستعلامات
    $pdo->exec($sql);
    
    echo "✅ تم إنشاء قاعدة البيانات والجداول بنجاح!<br>";
    echo "✅ قاعدة البيانات: al_moheeb_db<br>";
    echo "✅ الجداول: projects, admin_users<br>";
    echo "✅ المستخدم: admin / 123456<br>";
    echo "✅ يمكنك الآن استخدام لوحة التحكم";
    
} catch(PDOException $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>