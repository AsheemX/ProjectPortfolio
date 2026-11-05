<?php
include 'config.php';

header('Content-Type: application/json; charset=utf-8');

// إعدادات الرفع
$uploadDir = 'uploads/';
$maxFileSize = 10 * 1024 * 1024; // 10MB
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// إنشاء مجلد التحميلات إذا لم يكن موجوداً
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode([
            'success' => false, 
            'message' => 'فشل في إنشاء مجلد التحميلات'
        ]);
        exit;
    }
}

// التحقق من وجود ملف
if (!isset($_FILES['image'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'لم يتم رفع أي ملف'
    ]);
    exit;
}

$file = $_FILES['image'];

// التحقق من الأخطاء
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'الملف أكبر من الحجم المسموح به في الخادم',
        UPLOAD_ERR_FORM_SIZE => 'الملف أكبر من الحجم المسموح به في النموذج',
        UPLOAD_ERR_PARTIAL => 'تم رفع جزء من الملف فقط',
        UPLOAD_ERR_NO_FILE => 'لم يتم رفع أي ملف',
        UPLOAD_ERR_NO_TMP_DIR => 'المجلد المؤقت غير موجود',
        UPLOAD_ERR_CANT_WRITE => 'فشل في كتابة الملف على القرص',
        UPLOAD_ERR_EXTENSION => 'تم إيقاف الرفع بواسطة إضافة PHP'
    ];
    
    echo json_encode([
        'success' => false, 
        'message' => 'خطأ في الرفع: ' . ($errorMessages[$file['error']] ?? 'خطأ غير معروف')
    ]);
    exit;
}

// التحقق من الحجم
if ($file['size'] > $maxFileSize) {
    echo json_encode([
        'success' => false, 
        'message' => 'حجم الملف كبير جداً. الحد الأقصى 10MB'
    ]);
    exit;
}

// التحقق من النوع باستخدام mime_content_type للأمان
$fileType = mime_content_type($file['tmp_name']);
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode([
        'success' => false, 
        'message' => 'نوع الملف غير مسموح. المسموح: JPG, PNG, GIF, WebP'
    ]);
    exit;
}

// إنشاء اسم فريد للملف
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid() . '_' . time() . '.' . $fileExtension;
$targetFile = $uploadDir . $fileName;

// التحقق من أن الملف هو صورة حقيقية
if (!getimagesize($file['tmp_name'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'الملف ليس صورة صالحة'
    ]);
    exit;
}

// رفع الملف
if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    // التحقق من أن الملف تم رفعه بنجاح
    if (file_exists($targetFile)) {
        echo json_encode([
            'success' => true,
            'imageUrl' => $targetFile,
            'fileName' => $fileName,
            'message' => 'تم رفع الصورة بنجاح'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'فشل في حفظ الملف على الخادم'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'فشل في رفع الملف. يرجى المحاولة مرة أخرى'
    ]);
}
?>