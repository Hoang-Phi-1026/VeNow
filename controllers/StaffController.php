<?php
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/BaseController.php';

class StaffController extends BaseController {
   private $commentModel;
   private $eventModel;

   public function __construct() {
       parent::__construct();
       $this->commentModel = new Comment();
       $this->eventModel = new Event();
       
       // Kiểm tra quyền staff
       if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 3) {
           header('Location: ' . BASE_URL . '/login');
           exit;
       }
   }

   /**
    * Hiển thị trang quản lý bình luận
    */
   public function reviews() {
       // Lấy danh sách bình luận chờ duyệt
       $pendingComments = $this->commentModel->getPendingComments();
       
       require_once __DIR__ . '/../views/staff/reviews.php';
   }

   /**
    * Duyệt bình luận
    */
   public function approveReview($commentId) {
       // Kiểm tra method POST
       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           header('Location: ' . BASE_URL . '/reviews');
           exit;
       }

       // Duyệt bình luận
       if ($this->commentModel->approveComment($commentId)) {
           $_SESSION['success'] = 'Đã duyệt bình luận thành công';
       } else {
           $_SESSION['error'] = 'Có lỗi xảy ra khi duyệt bình luận';
       }

       header('Location: ' . BASE_URL . '/reviews');
       exit;
   }

   /**
    * Từ chối bình luận
    */
   public function rejectReview($commentId) {
       // Kiểm tra method POST
       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           header('Location: ' . BASE_URL . '/reviews');
           exit;
       }

       // Từ chối bình luận
       if ($this->commentModel->rejectComment($commentId)) {
           $_SESSION['success'] = 'Đã từ chối bình luận thành công';
       } else {
           $_SESSION['error'] = 'Có lỗi xảy ra khi từ chối bình luận';
       }

       header('Location: ' . BASE_URL . '/reviews');
       exit;
   }

   /**
    * Hiển thị trang sự kiện chờ duyệt
    */
   public function pendingEvents() {
       // Lấy danh sách sự kiện chờ duyệt
       $events = $this->eventModel->getPendingEvents();
       
       require_once __DIR__ . '/../views/staff/pending_events.php';
   }

   /**
    * Duyệt sự kiện
    */
   public function approveEvent() {
       // Kiểm tra method POST
       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           header('Location: ' . BASE_URL . '/staff/pending-events');
           exit;
       }

       $eventId = $_POST['event_id'] ?? null;
       if (!$eventId) {
           $_SESSION['error'] = 'Thiếu thông tin sự kiện';
           header('Location: ' . BASE_URL . '/staff/pending-events');
           exit;
       }

       // Duyệt sự kiện
       if ($this->eventModel->updateEventStatus($eventId, 'DA_DUYET')) {
           $_SESSION['success'] = 'Đã duyệt sự kiện thành công';
       } else {
           $_SESSION['error'] = 'Có lỗi xảy ra khi duyệt sự kiện';
       }

       header('Location: ' . BASE_URL . '/staff/pending-events');
       exit;
   }

   /**
    * Từ chối sự kiện
    */
   public function rejectEvent() {
       // Kiểm tra method POST
       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           header('Location: ' . BASE_URL . '/staff/pending-events');
           exit;
       }

       $eventId = $_POST['event_id'] ?? null;
       if (!$eventId) {
           $_SESSION['error'] = 'Thiếu thông tin sự kiện';
           header('Location: ' . BASE_URL . '/staff/pending-events');
           exit;
       }

       // Từ chối sự kiện
       if ($this->eventModel->updateEventStatus($eventId, 'TU_CHOI')) {
           $_SESSION['success'] = 'Đã từ chối sự kiện thành công';
       } else {
           $_SESSION['error'] = 'Có lỗi xảy ra khi từ chối sự kiện';
       }

       header('Location: ' . BASE_URL . '/staff/pending-events');
       exit;
   }

   /**
    * Hiển thị trang quản lý khiếu nại/tin nhắn từ khách hàng
    */
   public function complaints() {
       require_once __DIR__ . '/../views/staff/complaints.php';
   }

   /**
    * Hiển thị trang quản lý chat hỗ trợ
    */
   public function supportChat() {
       require_once __DIR__ . '/../views/staff/support_chat.php';
   }

   /**
    * Load danh sách sessions cho staff
    */
   public function loadSessions() {
       header('Content-Type: application/json; charset=utf-8');
       
       try {
           $status = $_GET['status'] ?? 'all';
           
           $sql = "
               SELECT 
                   s.*,
                   (SELECT message FROM support_messages sm WHERE sm.session_id = s.session_id ORDER BY sent_at DESC LIMIT 1) as last_message,
                   (SELECT COUNT(*) FROM support_messages sm WHERE sm.session_id = s.session_id AND sm.sender = 'user' AND sm.is_read = 0) as unread_count
               FROM support_sessions s
               WHERE 1=1
           ";
           
           $params = [];
           
           if ($status !== 'all') {
               $sql .= " AND s.status = ?";
               $params[] = $status;
           }
           
           $sql .= " ORDER BY s.updated_at DESC";
           
           $stmt = $this->db->prepare($sql);
           $stmt->execute($params);
           
           $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
           
           echo json_encode([
               'success' => true,
               'sessions' => $sessions
           ]);
           
       } catch (Exception $e) {
           echo json_encode([
               'success' => false,
               'message' => 'Lỗi hệ thống: ' . $e->getMessage()
           ]);
       }
   }

   /**
    * Load tin nhắn cho một session
    */
   public function loadMessages() {
       header('Content-Type: application/json; charset=utf-8');
       
       try {
           $session_id = $_GET['session_id'] ?? '';
           if (empty($session_id)) {
               echo json_encode(['success' => false, 'message' => 'Session ID không hợp lệ']);
               exit;
           }
           
           $staff_id = $_SESSION['user']['ma_nguoi_dung'];
           
           // Lấy thông tin session
           $stmt = $this->db->prepare("SELECT * FROM support_sessions WHERE session_id = ?");
           $stmt->execute([$session_id]);
           $session = $stmt->fetch(PDO::FETCH_ASSOC);
           
           if (!$session) {
               echo json_encode(['success' => false, 'message' => 'Session không tồn tại']);
               exit;
           }
           
           // Gán staff cho session nếu chưa có
           if (!$session['assigned_staff_id']) {
               $stmt = $this->db->prepare("
                   UPDATE support_sessions 
                   SET assigned_staff_id = ?, status = 'active' 
                   WHERE session_id = ?
               ");
               $stmt->execute([$staff_id, $session_id]);
               $session['assigned_staff_id'] = $staff_id;
               $session['status'] = 'active';
           }
           
           // Lấy tin nhắn
           $stmt = $this->db->prepare("
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
           
           // Đánh dấu tin nhắn từ user là đã đọc
           $stmt = $this->db->prepare("
               UPDATE support_messages 
               SET is_read = 1 
               WHERE session_id = ? AND sender = 'user' AND is_read = 0
           ");
           $stmt->execute([$session_id]);
           
           echo json_encode([
               'success' => true,
               'messages' => $messages,
               'session' => $session
           ]);
           
       } catch (Exception $e) {
           echo json_encode([
               'success' => false,
               'message' => 'Lỗi hệ thống: ' . $e->getMessage()
           ]);
       }
   }

   /**
    * Gửi tin nhắn từ staff
    */
   public function sendMessage() {
       header('Content-Type: application/json; charset=utf-8');
       
       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
           exit;
       }

       try {
           $session_id = $_POST['session_id'] ?? '';
           $message = trim(strip_tags($_POST['message'] ?? ''));
           
           if (empty($session_id) || empty($message)) {
               echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
               exit;
           }
           
           $staff_id = $_SESSION['user']['ma_nguoi_dung'];
           $staff_name = $_SESSION['user']['ho_ten'];
           
           // Lưu tin nhắn
           $stmt = $this->db->prepare("
               INSERT INTO support_messages (session_id, staff_id, sender, staff_name, message) 
               VALUES (?, ?, 'staff', ?, ?)
           ");
           $stmt->execute([$session_id, $staff_id, $staff_name, $message]);
           
           // Cập nhật session
           $stmt = $this->db->prepare("
               UPDATE support_sessions 
               SET status = 'active', assigned_staff_id = ?, updated_at = CURRENT_TIMESTAMP 
               WHERE session_id = ?
           ");
           $stmt->execute([$staff_id, $session_id]);
           
           echo json_encode(['success' => true, 'message' => 'Tin nhắn đã được gửi']);
           
       } catch (Exception $e) {
           echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
       }
   }

   /**
    * Kết thúc session chat
    */
   public function closeSession() {
       header('Content-Type: application/json; charset=utf-8');
       
       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
           exit;
       }

       try {
           $session_id = $_POST['session_id'] ?? '';
           
           if (empty($session_id)) {
               echo json_encode(['success' => false, 'message' => 'Session ID không hợp lệ']);
               exit;
           }
           
           $stmt = $this->db->prepare("
               UPDATE support_sessions 
               SET status = 'closed', updated_at = CURRENT_TIMESTAMP 
               WHERE session_id = ?
           ");
           $stmt->execute([$session_id]);
           
           echo json_encode(['success' => true, 'message' => 'Đã kết thúc cuộc trò chuyện']);
           
       } catch (Exception $e) {
           echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
       }
   }
}
