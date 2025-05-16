<?php
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/BaseController.php';

class CommentController extends BaseController {
    private $commentModel;

    public function __construct() {
        parent::__construct();
        $this->commentModel = new Comment();
    }

    /**
     * Xử lý thêm bình luận mới
     */
    public function addComment() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Bạn cần đăng nhập để bình luận';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Kiểm tra phương thức POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }

        // Lấy dữ liệu từ form
        $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        $content = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $userId = $_SESSION['user']['ma_nguoi_dung'];

        // Validate dữ liệu
        $errors = [];
        if (empty($content)) {
            $errors[] = 'Nội dung bình luận không được để trống';
        }
        if ($rating < 1 || $rating > 5) {
            $errors[] = 'Điểm đánh giá phải từ 1 đến 5';
        }
        if ($eventId <= 0) {
            $errors[] = 'Sự kiện không hợp lệ';
        }

        // Kiểm tra xem người dùng đã bình luận cho sự kiện này chưa
        if ($this->commentModel->hasUserCommented($eventId, $userId)) {
            $errors[] = 'Bạn đã bình luận cho sự kiện này rồi';
        }

        // Nếu có lỗi, quay lại trang trước với thông báo lỗi
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Thêm bình luận vào database
        $result = $this->commentModel->addComment($eventId, $userId, $content, $rating);
        
        if ($result) {
            $_SESSION['success'] = 'Cảm ơn bạn đã đánh giá! Bình luận của bạn đang chờ duyệt.';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra, vui lòng thử lại sau';
        }

        // Quay lại trang chi tiết sự kiện
        header('Location: ' . BASE_URL . '/event/' . $eventId);
        exit;
    }
}
