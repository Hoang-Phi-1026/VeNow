<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

try {
    $session_id = session_id();
    // Sử dụng singleton để lấy kết nối PDO
    $db = Database::getInstance()->getConnection();
    
    // Xóa tin nhắn
    $stmt = $db->prepare("DELETE FROM support_messages WHERE session_id = ?");
    $stmt->execute([$session_id]);
    
    // Xóa session
    $stmt = $db->prepare("DELETE FROM support_sessions WHERE session_id = ?");
    $stmt->execute([$session_id]);
    
    echo json_encode(['success' => true, 'message' => 'Đã xóa lịch sử chat']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>