<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Kiểm tra method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false, 
            'message' => 'Phương thức không hợp lệ'
        ]);
        exit;
    }

    // Kiểm tra session
    if (!isset($_SESSION['user'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Chưa đăng nhập'
        ]);
        exit;
    }

    // Kiểm tra quyền staff
    $user = $_SESSION['user'];
    $isStaff = false;
    
    if (isset($user['vai_tro']) && $user['vai_tro'] == 3) {
        $isStaff = true;
    } elseif (isset($user['ma_vai_tro']) && $user['ma_vai_tro'] == 3) {
        $isStaff = true;
    }
    
    if (!$isStaff) {
        echo json_encode([
            'success' => false, 
            'message' => 'Không có quyền truy cập'
        ]);
        exit;
    }

    // Lấy dữ liệu POST
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $session_id = $input['session_id'] ?? '';
    
    if (empty($session_id)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Session ID không hợp lệ'
        ]);
        exit;
    }
    
    // Kết nối database (sửa cho đúng kiểu singleton)
    $db = Database::getInstance()->getConnection();
    
    // Kiểm tra session tồn tại
    $stmt = $db->prepare("SELECT * FROM support_sessions WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        echo json_encode([
            'success' => false, 
            'message' => 'Session không tồn tại'
        ]);
        exit;
    }
    
    // Cập nhật trạng thái session
    $stmt = $db->prepare("
        UPDATE support_sessions 
        SET status = 'closed', updated_at = CURRENT_TIMESTAMP 
        WHERE session_id = ?
    ");
    $result = $stmt->execute([$session_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Đã đóng cuộc trò chuyện thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Không thể đóng cuộc trò chuyện'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error in staff_close_session: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cơ sở dữ liệu',
        'debug' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in staff_close_session: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống',
        'debug' => 'Error: ' . $e->getMessage()
    ]);
}
?>