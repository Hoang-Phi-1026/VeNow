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
}
