<?php
// ✅ Tránh gọi session_start() nếu session đã tồn tại
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Không hiển thị lỗi ra màn hình
ini_set('log_errors', 1);     // Ghi lỗi vào log file

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

    // Kiểm tra quyền staff (vai_tro = 3 hoặc ma_vai_tro = 3)
    $user = $_SESSION['user'];
    $isStaff = false;

    if (($user['vai_tro'] ?? null) == 3 || ($user['ma_vai_tro'] ?? null) == 3) {
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

    // ✅ Fix: Tạo instance rồi gọi getConnection() đúng cách
    $dbInstance = Database::getInstance();
    $db = $dbInstance->getConnection();

    // Test kết nối
    $db->query("SELECT 1");

    // Lấy tham số
    $status = $_GET['status'] ?? 'all';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Build query (Đổi bảng users => nguoidung, cột id => ma_nguoi_dung, email => email, ho_ten => ho_ten)
    $sql = "
        SELECT 
            s.*,
            u.email as user_email,
            u.ho_ten as user_name,
            nd.ho_ten as assigned_staff_name,
            (SELECT message FROM support_messages sm 
             WHERE sm.session_id = s.session_id 
             ORDER BY sent_at DESC LIMIT 1) as last_message,
            (SELECT COUNT(*) FROM support_messages sm 
             WHERE sm.session_id = s.session_id 
             AND sm.sender = 'user' 
             AND sm.is_read = 0) as unread_count,
            (SELECT sent_at FROM support_messages sm 
             WHERE sm.session_id = s.session_id 
             ORDER BY sent_at DESC LIMIT 1) as last_message_time
        FROM support_sessions s
        LEFT JOIN nguoidung u ON s.user_id = u.ma_nguoi_dung
        LEFT JOIN nguoidung nd ON s.assigned_staff_id = nd.ma_nguoi_dung
        WHERE 1=1
    ";

    $params = [];

    if ($status !== 'all') {
        $sql .= " AND s.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY s.updated_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $countSql = "
        SELECT COUNT(*) as total
        FROM support_sessions s
        WHERE 1=1
    ";

    $countParams = [];
    if ($status !== 'all') {
        $countSql .= " AND s.status = ?";
        $countParams[] = $status;
    }

    $countStmt = $db->prepare($countSql);
    $countStmt->execute($countParams);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $totalPages = ceil($totalCount / $limit);

    // Format dữ liệu sessions
    foreach ($sessions as &$session) {
        $session['unread_count'] = (int)$session['unread_count'];
        $session['last_message'] = $session['last_message'] ? htmlspecialchars($session['last_message']) : '';
        $session['user_email'] = $session['user_email'] ?: 'Không có email';
        $session['user_name'] = $session['user_name'] ?: 'Khách hàng';
        $session['assigned_staff_name'] = $session['assigned_staff_name'] ?: '';
    }

    echo json_encode([
        'success' => true,
        'sessions' => $sessions,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => (int)$totalCount
        ],
        'debug' => [
            'status_filter' => $status,
            'query_params' => $params,
            'user_info' => $user
        ]
    ]);
} catch (PDOException $e) {
    error_log("Database error in staff_load_sessions: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cơ sở dữ liệu',
        'debug' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in staff_load_sessions: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống',
        'debug' => 'Error: ' . $e->getMessage()
    ]);
}
?>