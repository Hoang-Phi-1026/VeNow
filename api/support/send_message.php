<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

if (empty($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống!']);
    exit;
}

try {
    $session_id = session_id();
    $user_id = isset($_SESSION['user']['ma_nguoi_dung']) ? $_SESSION['user']['ma_nguoi_dung'] : null;
    $user_name = isset($_SESSION['user']['ho_ten']) ? $_SESSION['user']['ho_ten'] : 'Khách';
    $message = trim(strip_tags($_POST['message']));
    
    if (strlen($message) > 1000) {
        echo json_encode(['success' => false, 'message' => 'Tin nhắn quá dài (tối đa 1000 ký tự)']);
        exit;
    }
    
    // Sử dụng singleton để lấy kết nối PDO
    $db = Database::getInstance()->getConnection();
    
    // Lưu tin nhắn
    $stmt = $db->prepare("
        INSERT INTO support_messages (session_id, user_id, sender, user_name, message) 
        VALUES (?, ?, 'user', ?, ?)
    ");
    $stmt->execute([$session_id, $user_id, $user_name, $message]);
    
    // Cập nhật trạng thái session
    $stmt = $db->prepare("
        UPDATE support_sessions 
        SET status = 'waiting', updated_at = CURRENT_TIMESTAMP 
        WHERE session_id = ?
    ");
    $stmt->execute([$session_id]);
    
    echo json_encode(['success' => true, 'message' => 'Tin nhắn đã được gửi']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>