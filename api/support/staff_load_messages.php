<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Kiểm tra session
    if (!isset($_SESSION['user'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Chưa đăng nhập',
            'debug' => 'Session not found'
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
            'message' => 'Không có quyền truy cập',
            'debug' => 'User role: ' . json_encode($user)
        ]);
        exit;
    }

    // Lấy session_id
    $session_id = $_GET['session_id'] ?? '';
    if (empty($session_id)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Session ID không hợp lệ',
            'debug' => 'Empty session_id'
        ]);
        exit;
    }

    // Kết nối database (fix lỗi gọi non-static)
    $db = Database::getInstance()->getConnection();
    
    // Test database connection
    $db->query("SELECT 1");
    
    $staff_id = $user['ma_nguoi_dung'] ?? $user['id'];
    
    // Kiểm tra session tồn tại
    $stmt = $db->prepare("SELECT * FROM support_sessions WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        echo json_encode([
            'success' => false, 
            'message' => 'Session không tồn tại',
            'debug' => 'Session ID: ' . $session_id
        ]);
        exit;
    }
    
    // Gán staff cho session nếu chưa có
    if (!$session['assigned_staff_id']) {
        $stmt = $db->prepare("
            UPDATE support_sessions 
            SET assigned_staff_id = ?, status = 'active', updated_at = CURRENT_TIMESTAMP
            WHERE session_id = ?
        ");
        $result = $stmt->execute([$staff_id, $session_id]);
        
        if ($result) {
            $session['assigned_staff_id'] = $staff_id;
            $session['status'] = 'active';
        }
    }
    
    // Lấy tin nhắn
    $stmt = $db->prepare("
        SELECT 
            id,
            sender, 
            user_name, 
            staff_name, 
            message, 
            sent_at,
            is_read
        FROM support_messages 
        WHERE session_id = ? 
        ORDER BY sent_at ASC, id ASC
    ");
    $stmt->execute([$session_id]);
    
    $messages = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $messages[] = [
            'id' => $row['id'],
            'sender' => $row['sender'],
            'user_name' => $row['user_name'] ?: 'Khách hàng',
            'staff_name' => $row['staff_name'] ?: 'Nhân viên',
            'message' => htmlspecialchars($row['message']),
            'sent_at' => $row['sent_at'],
            'is_read' => (bool)$row['is_read']
        ];
    }
    
    // Đánh dấu tin nhắn từ user là đã đọc
    $stmt = $db->prepare("
        UPDATE support_messages 
        SET is_read = 1 
        WHERE session_id = ? AND sender = 'user' AND is_read = 0
    ");
    $stmt->execute([$session_id]);
    
    // Lấy thông tin user
    $userInfo = null;
    if ($session['user_id']) {
        // Đổi users sang nguoidung nếu đúng bảng của bạn
        $stmt = $db->prepare("SELECT ma_nguoi_dung as id, email, ho_ten FROM nguoidung WHERE ma_nguoi_dung = ?");
        $stmt->execute([$session['user_id']]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'session' => $session,
        'user_info' => $userInfo,
        'debug' => [
            'session_id' => $session_id,
            'staff_id' => $staff_id,
            'message_count' => count($messages),
            'session_status' => $session['status']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in staff_load_messages: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cơ sở dữ liệu',
        'debug' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in staff_load_messages: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống',
        'debug' => 'Error: ' . $e->getMessage()
    ]);
}
?>