<?php
include 'config.php';

class API {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // تسجيل الدخول - محدث لدعم إيقاف كلمة المرور الافتراضية بعد التغيير
    public function login($username, $password) {
        try {
            // البحث في قاعدة البيانات أولاً
            $query = "SELECT * FROM admin_users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // التحقق من كلمة المرور المشفرة أولاً
                if (password_verify($password, $user['password_hash'])) {
                    return ['success' => true, 'message' => 'تم الدخول بنجاح'];
                }
                
                // للمستخدم admin فقط: التحقق من كلمة المرور الافتراضية إذا لم يكن لديه كلمة مرور مشفرة في قاعدة البيانات
                if ($username === 'admin' && $password === '123456' && empty($user['password_hash'])) {
                    return ['success' => true, 'message' => 'تم الدخول بنجاح'];
                }
            } else {
                // إذا لم يوجد المستخدم في قاعدة البيانات، نسمح للمستخدم admin بالدخول بالكلمة الافتراضية
                if ($username === 'admin' && $password === '123456') {
                    return ['success' => true, 'message' => 'تم الدخول بنجاح'];
                }
            }
            
            return ['success' => false, 'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة'];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'خطأ في النظام أثناء الدخول'];
        }
    }

    // الحصول على جميع المشاريع
    public function getProjects($category = null) {
        try {
            $query = "SELECT * FROM projects";
            
            if ($category && $category !== 'all') {
                $query .= " WHERE category = :category";
            }
            
            $query .= " ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if ($category && $category !== 'all') {
                $stmt->bindParam(":category", $category);
            }
            
            $stmt->execute();
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // إضافة للتصحيح - طباعة المشاريع في سجل الأخطاء
            error_log("Projects retrieved: " . json_encode($projects));
            error_log("Filter category: " . $category);
            return [
                'success' => true,
                'data' => $projects,
                'count' => count($projects)
            ];
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => $e->getMessage(),
                'data' => [],
                'count' => 0
            ];
        }
    }

    // إضافة مشروع جديد
    public function addProject($data) {
        try {

            // تسجيل جميع البيانات الواردة للتصحيح
            error_log("📥 بيانات المشروع الواردة: " . json_encode($data));

            // التحقق من البيانات المطلوبة
            if (empty($data['title']) || empty($data['category']) || empty($data['image_url'])) {
                return [
                    'success' => false, 
                    'message' => 'جميع الحقول المطلوبة يجب ملؤها (العنوان، التصنيف، الصورة)'
                ];
            }

            error_log("✅ جميع الحقول المطلوبة موجودة");
        
            // التحقق من القيم الفعلية
            error_log("🔍 قيمة التصنيف: " . $data['category']);
            error_log("🔍 قيمة العنوان: " . $data['title']);
            error_log("🔍 رابط الصورة: " . $data['image_url']);

            $query = "INSERT INTO projects 
                     (title, category, description, image_url, location, duration) 
                     VALUES 
                     (:title, :category, :description, :image_url, :location, :duration)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":category", $data['category']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":image_url", $data['image_url']);
            $stmt->bindParam(":location", $data['location']);
            $stmt->bindParam(":duration", $data['duration']);

            if ($stmt->execute()) {
                $projectId = $this->conn->lastInsertId();
                error_log("✅ تم إضافة المشروع بنجاح - ID: " . $projectId);
                
                // التحقق من الإضافة بالاستعلام عن المشروع المضاف
                $checkQuery = "SELECT * FROM projects WHERE id = :id";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(":id", $projectId);
                $checkStmt->execute();
                $addedProject = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                error_log("🔍 المشروع المضاف: " . json_encode($addedProject));
                
                return [
                    'success' => true,
                    'message' => 'تم إضافة المشروع بنجاح',
                    'project_id' => $projectId
                ];
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ فشل في تنفيذ الاستعلام: " . json_encode($errorInfo));
                return [
                    'success' => false, 
                    'message' => 'فشل في إضافة المشروع: ' . $errorInfo[2]
                ];
            }
        } catch (Exception $e) {
            error_log("Add project error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'خطأ في النظام: ' . $e->getMessage()
            ];
        }
    }

    // حذف مشروع
    public function deleteProject($id) {
        try {
            // الحصول على رابط الصورة أولاً لحذفها
            $query = "SELECT image_url FROM projects WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$project) {
                return ['success' => false, 'message' => 'المشروع غير موجود'];
            }

            // حذف المشروع من قاعدة البيانات
            $query = "DELETE FROM projects WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);

            if ($stmt->execute()) {
                // حذف الصورة من المجلد إذا كانت محلية
                if ($project['image_url'] && file_exists($project['image_url'])) {
                    unlink($project['image_url']);
                }
                return ['success' => true, 'message' => 'تم حذف المشروع بنجاح'];
            } else {
                return ['success' => false, 'message' => 'فشل في حذف المشروع'];
            }
        } catch (Exception $e) {
            error_log("Delete project error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'خطأ في النظام: ' . $e->getMessage()
            ];
        }
    }

    // الحصول على جميع المستخدمين
    public function getUsers() {
        try {
            $query = "SELECT id, username, created_at FROM admin_users ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $users,
                'count' => count($users)
            ];
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => $e->getMessage(),
                'data' => [],
                'count' => 0
            ];
        }
    }

    // إضافة مستخدم جديد
    public function addUser($username, $password) {
        try {
            // التحقق من البيانات
            if (empty($username) || empty($password)) {
                return ['success' => false, 'message' => 'اسم المستخدم وكلمة المرور مطلوبان'];
            }

            // التحقق من عدم وجود مستخدم بنفس الاسم
            $checkQuery = "SELECT id FROM admin_users WHERE username = :username";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":username", $username);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'اسم المستخدم موجود مسبقاً'];
            }

            // تشفير كلمة المرور
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // إضافة المستخدم
            $query = "INSERT INTO admin_users (username, password_hash) VALUES (:username, :password_hash)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password_hash", $passwordHash);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'تم إضافة المستخدم بنجاح',
                    'user_id' => $this->conn->lastInsertId()
                ];
            } else {
                return ['success' => false, 'message' => 'فشل في إضافة المستخدم'];
            }
        } catch (Exception $e) {
            error_log("Add user error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'خطأ في النظام: ' . $e->getMessage()
            ];
        }
    }

    // تغيير كلمة المرور
    public function changePassword($username, $currentPassword, $newPassword) {
        try {
            // التحقق من البيانات
            if (empty($username) || empty($currentPassword) || empty($newPassword)) {
                return ['success' => false, 'message' => 'جميع الحقول مطلوبة'];
            }

            // التحقق من وجود المستخدم
            $query = "SELECT password_hash FROM admin_users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // إذا لم يوجد المستخدم في قاعدة البيانات، ننشئه (خاصة للمستخدم admin)
            if (!$user) {
                if ($username === 'admin') {
                    // إنشاء مستخدم admin جديد
                    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $insertQuery = "INSERT INTO admin_users (username, password_hash) VALUES (:username, :password_hash)";
                    $insertStmt = $this->conn->prepare($insertQuery);
                    $insertStmt->bindParam(":username", $username);
                    $insertStmt->bindParam(":password_hash", $passwordHash);
                    
                    if ($insertStmt->execute()) {
                        return ['success' => true, 'message' => 'تم إنشاء كلمة مرور جديدة للمستخدم admin'];
                    } else {
                        return ['success' => false, 'message' => 'فشل في إنشاء كلمة مرور جديدة'];
                    }
                } else {
                    return ['success' => false, 'message' => 'المستخدم غير موجود'];
                }
            }

            // التحقق من كلمة المرور الحالية
            $isCurrentPasswordValid = false;
            
            // حالة خاصة: إذا كان المستخدم admin ولم يكن لديه كلمة مرور مشفرة بعد
            if ($username === 'admin' && empty($user['password_hash']) && $currentPassword === '123456') {
                $isCurrentPasswordValid = true;
            } 
            // حالة عادية: التحقق من كلمة المرور المشفرة
            elseif (password_verify($currentPassword, $user['password_hash'])) {
                $isCurrentPasswordValid = true;
            }

            if (!$isCurrentPasswordValid) {
                return ['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة'];
            }

            // تحديث كلمة المرور
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE admin_users SET password_hash = :password_hash WHERE username = :username";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":password_hash", $newPasswordHash);
            $updateStmt->bindParam(":username", $username);

            if ($updateStmt->execute()) {
                return ['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح'];
            } else {
                return ['success' => false, 'message' => 'فشل في تغيير كلمة المرور'];
            }
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'خطأ في النظام: ' . $e->getMessage()
            ];
        }
    }

    // إعادة تعيين كلمة المرور
    public function resetPassword($username) {
        try {
            // التحقق من البيانات
            if (empty($username)) {
                return ['success' => false, 'message' => 'اسم المستخدم مطلوب'];
            }

            // التحقق من وجود المستخدم
            $query = "SELECT id FROM admin_users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // إذا لم يوجد المستخدم، ننشئه
            if (!$user) {
                $defaultPassword = '123456';
                $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
                
                $insertQuery = "INSERT INTO admin_users (username, password_hash) VALUES (:username, :password_hash)";
                $insertStmt = $this->conn->prepare($insertQuery);
                $insertStmt->bindParam(":username", $username);
                $insertStmt->bindParam(":password_hash", $passwordHash);
                
                if ($insertStmt->execute()) {
                    return ['success' => true, 'message' => 'تم إنشاء المستخدم وكلمة المرور 123456'];
                } else {
                    return ['success' => false, 'message' => 'فشل في إنشاء المستخدم'];
                }
            }

            // إعادة تعيين كلمة المرور إلى 123456
            $defaultPassword = '123456';
            $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

            $updateQuery = "UPDATE admin_users SET password_hash = :password_hash WHERE username = :username";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":password_hash", $passwordHash);
            $updateStmt->bindParam(":username", $username);

            if ($updateStmt->execute()) {
                return ['success' => true, 'message' => 'تم إعادة تعيين كلمة المرور إلى 123456'];
            } else {
                return ['success' => false, 'message' => 'فشل في إعادة تعيين كلمة المرور'];
            }
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'خطأ في النظام: ' . $e->getMessage()
            ];
        }
    }

    // حذف مستخدم
    public function deleteUser($id) {
        try {
            // التحقق من البيانات
            if (empty($id)) {
                return ['success' => false, 'message' => 'معرف المستخدم مطلوب'];
            }

            // منع حذف المستخدم admin
            $checkQuery = "SELECT username FROM admin_users WHERE id = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":id", $id);
            $checkStmt->execute();
            $user = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['username'] === 'admin') {
                return ['success' => false, 'message' => 'لا يمكن حذف المستخدم admin'];
            }

            // حذف المستخدم
            $query = "DELETE FROM admin_users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'تم حذف المستخدم بنجاح'];
            } else {
                return ['success' => false, 'message' => 'فشل في حذف المستخدم'];
            }
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'خطأ في النظام: ' . $e->getMessage()
            ];
        }
    }

    // التأكد من وجود المستخدم admin في قاعدة البيانات
    public function ensureAdminUser() {
        try {
            $checkQuery = "SELECT id FROM admin_users WHERE username = 'admin'";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute();

            if ($checkStmt->rowCount() === 0) {
                // إضافة المستخدم admin بدون كلمة مرور مشفرة (سيستخدم الكلمة الافتراضية)
                $insertQuery = "INSERT INTO admin_users (username, password_hash) VALUES ('admin', '')";
                $insertStmt = $this->conn->prepare($insertQuery);
                $insertStmt->execute();
            }
        } catch (Exception $e) {
            error_log("Ensure admin user error: " . $e->getMessage());
        }
    }
}

// معالجة الطلبات مع تصحيح الأخطاء
$api = new API();

// التأكد من وجود المستخدم admin في بداية التشغيل
$api->ensureAdminUser();

$method = $_SERVER['REQUEST_METHOD'];

// إعدادات الـ headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($method == 'POST') {
    $action = $_POST['action'] ?? '';
    
    // تسجيل الطلب للتصحيح
    error_log("API POST Request - Action: " . $action);
    
    switch ($action) {
        case 'login':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            echo json_encode($api->login($username, $password));
            break;

        case 'add_project':
            $project_data = [
                'title' => $_POST['title'] ?? '',
                'category' => $_POST['category'] ?? '',
                'description' => $_POST['description'] ?? '',
                'image_url' => $_POST['image_url'] ?? '',
                'location' => $_POST['location'] ?? '',
                'duration' => $_POST['duration'] ?? ''
            ];
            echo json_encode($api->addProject($project_data));
            break;

        case 'delete_project':
            $id = $_POST['id'] ?? '';
            echo json_encode($api->deleteProject($id));
            break;

        case 'add_user':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            echo json_encode($api->addUser($username, $password));
            break;

        case 'change_password':
            $username = $_POST['username'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            echo json_encode($api->changePassword($username, $currentPassword, $newPassword));
            break;

        case 'reset_password':
            $username = $_POST['username'] ?? '';
            echo json_encode($api->resetPassword($username));
            break;

        case 'delete_user':
            $id = $_POST['id'] ?? '';
            echo json_encode($api->deleteUser($id));
            break;

        default:
            echo json_encode([
                'success' => false, 
                'message' => 'الإجراء غير معروف: ' . $action
            ]);
    }
} elseif ($method == 'GET') {
    $action = $_GET['action'] ?? '';
    
    error_log("API GET Request - Action: " . $action);
    
    switch ($action) {
        case 'get_projects':
            $category = $_GET['category'] ?? 'all';
            echo json_encode($api->getProjects($category));
            break;

        case 'get_users':
            echo json_encode($api->getUsers());
            break;

        default:
            echo json_encode([
                'success' => false, 
                'message' => 'الإجراء غير معروف: ' . $action
            ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'طريقة الطلب غير مدعومة: ' . $method
    ]);
}
?>