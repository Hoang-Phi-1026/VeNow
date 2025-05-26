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
    $message = trim(strip_tags($input['message'] ?? ''));
    
    if (empty($session_id) || empty($message)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Dữ liệu không hợp lệ',
            'debug' => [
                'session_id' => $session_id,
                'message_length' => strlen($message)
            ]
        ]);
        exit;
    }
    
    // Kết nối database (sửa lỗi: gọi đúng kiểu singleton)
    $db = Database::getInstance()->getConnection();
    
    $staff_id = $user['ma_nguoi_dung'] ?? $user['id'];
    $staff_name = $user['ho_ten'] ?? 'Nhân viên';
    
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
    
    // Bắt đầu transaction
    $db->beginTransaction();
    
    try {
        // Lưu tin nhắn
        $stmt = $db->prepare("
            INSERT INTO support_messages (session_id, staff_id, sender, staff_name, message, sent_at) 
            VALUES (?, ?, 'staff', ?, ?, NOW())
        ");
        $stmt->execute([$session_id, $staff_id, $staff_name, $message]);
        
        // Cập nhật session
        $stmt = $db->prepare("
            UPDATE support_sessions 
            SET status = 'active', assigned_staff_id = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE session_id = ?
        ");
        $stmt->execute([$staff_id, $session_id]);
        
        $db->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Tin nhắn đã được gửi',
            'data' => [
                'sender' => 'staff',
                'staff_name' => $staff_name,
                'message' => htmlspecialchars($message),
                'sent_at' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in staff_send_message: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cơ sở dữ liệu',
        'debug' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in staff_send_message: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống',
        'debug' => 'Error: ' . $e->getMessage()
    ]);
}
?>