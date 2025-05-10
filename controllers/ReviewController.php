<?php

class ReviewController extends BaseController {
    
    public function index() {
        // Check if user is logged in and is staff
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 3) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Get all pending comments
        $pendingComments = $this->getPendingComments();
        
        // Render the view
        $this->render('reviews/index', [
            'pendingComments' => $pendingComments
        ]);
    }
    
    public function approve() {
        // Check if user is logged in and is staff
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 3) {
            $_SESSION['error'] = 'Bạn không có quyền thực hiện hành động này.';
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Check if comment_id is provided
        if (!isset($_POST['comment_id'])) {
            $_SESSION['error'] = 'Thiếu thông tin bình luận.';
            header('Location: ' . BASE_URL . '/reviews');
            exit;
        }
        
        $commentId = $_POST['comment_id'];
        
        // Approve the comment
        $result = $this->approveComment($commentId);
        
        if ($result) {
            $_SESSION['success'] = 'Đã duyệt bình luận thành công.';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi duyệt bình luận.';
        }
        
        header('Location: ' . BASE_URL . '/reviews');
        exit;
    }
    
    public function reject() {
        // Check if user is logged in and is staff
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 3) {
            $_SESSION['error'] = 'Bạn không có quyền thực hiện hành động này.';
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Check if comment_id is provided
        if (!isset($_POST['comment_id'])) {
            $_SESSION['error'] = 'Thiếu thông tin bình luận.';
            header('Location: ' . BASE_URL . '/reviews');
            exit;
        }
        
        $commentId = $_POST['comment_id'];
        
        // Reject the comment
        $result = $this->rejectComment($commentId);
        
        if ($result) {
            $_SESSION['success'] = 'Đã từ chối bình luận thành công.';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi từ chối bình luận.';
        }
        
        header('Location: ' . BASE_URL . '/reviews');
        exit;
    }
    
    private function getPendingComments() {
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $query = "SELECT bl.*, sk.ten_su_kien, u.ho_ten, u.avatar 
                 FROM binh_luan bl
                 JOIN su_kien sk ON bl.ma_su_kien = sk.ma_su_kien
                 JOIN nguoi_dung u ON bl.ma_nguoi_dung = u.ma_nguoi_dung
                 WHERE bl.trang_thai = 'pending'
                 ORDER BY bl.ngay_tao DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function approveComment($commentId) {
        try {
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $query = "UPDATE binh_luan SET trang_thai = 'approved' WHERE ma_binh_luan = :comment_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error approving comment: ' . $e->getMessage());
            return false;
        }
    }
    
    private function rejectComment($commentId) {
        try {
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $query = "UPDATE binh_luan SET trang_thai = 'rejected' WHERE ma_binh_luan = :comment_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error rejecting comment: ' . $e->getMessage());
            return false;
        }
    }
}
