<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $session_id = session_id();
    $user_id = isset($_SESSION['user']['ma_nguoi_dung']) ? $_SESSION['user']['ma_nguoi_dung'] : null;
    $user_name = isset($_SESSION['user']['ho_ten']) ? $_SESSION['user']['ho_ten'] : null;
    $user_email = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : null;
    
    // Sử dụng singleton để lấy kết nối PDO
    $db = Database::getInstance()->getConnection();
    
    // Tạo hoặc cập nhật session
    $stmt = $db->prepare("
        INSERT INTO support_sessions (session_id, user_id, user_name, user_email) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            user_id = VALUES(user_id),
            user_name = VALUES(user_name),
            user_email = VALUES(user_email),
            updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$session_id, $user_id, $user_name, $user_email]);
    
    // Lấy thông tin session
    $stmt = $db->prepare("SELECT * FROM support_sessions WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Lấy tin nhắn
    $stmt = $db->prepare("
        SELECT sender, user_name, staff_name, message, sent_at 
        FROM support_messages 
        WHERE session_id = ? 
        ORDER BY sent_at ASC, id ASC
    ");
    $stmt->execute([$session_id]);
    
    $messages = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $messages[] = [
            'sender' => $row['sender'],
            'user_name' => $row['user_name'],
            'staff_name' => $row['staff_name'],
            'message' => htmlspecialchars($row['message']),
            'sent_at' => $row['sent_at']
        ];
    }
    
    // Đánh dấu tin nhắn từ staff là đã đọc
    $stmt = $db->prepare("
        UPDATE support_messages 
        SET is_read = 1 
        WHERE session_id = ? AND sender = 'staff' AND is_read = 0
    ");
    $stmt->execute([$session_id]);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'session_status' => $session['status'] ?? 'waiting',
        'assigned_staff' => $session['assigned_staff_id'] ?? null
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>