<?php

class ReviewController extends BaseController {
    
    public function index() {
        // Check if user is logged in and is staff
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 3) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Get all pending comments with proper debugging
        $pendingComments = $this->getPendingComments();
        
        // Log the data for debugging
        error_log('Pending comments data: ' . print_r($pendingComments, true));
        
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
        try {
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // First, let's get the table structure to understand the field names
            $tableQuery = "DESCRIBE binh_luan";
            $tableStmt = $db->prepare($tableQuery);
            $tableStmt->execute();
            $tableStructure = $tableStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('binh_luan table structure: ' . print_r($tableStructure, true));
            
            $tableQuery = "DESCRIBE nguoi_dung";
            $tableStmt = $db->prepare($tableQuery);
            $tableStmt->execute();
            $tableStructure = $tableStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('nguoi_dung table structure: ' . print_r($tableStructure, true));
            
            // Now let's try different queries to find the right one
            
            // Query 1: Using the original field names
            $query = "SELECT bl.*, sk.ten_su_kien, u.ho_ten, u.avatar 
                     FROM binh_luan bl
                     JOIN su_kien sk ON bl.ma_su_kien = sk.ma_su_kien
                     JOIN nguoi_dung u ON bl.ma_nguoi_dung = u.ma_nguoi_dung
                     WHERE bl.trang_thai = 'pending'
                     ORDER BY bl.ngay_tao DESC";
            
            try {
                $stmt = $db->prepare($query);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($results) > 0) {
                    error_log('Query 1 successful with ' . count($results) . ' results');
                    return $results;
                }
            } catch (PDOException $e) {
                error_log('Query 1 failed: ' . $e->getMessage());
            }
            
            // Query 2: Using alternative field names
            $query = "SELECT bl.*, sk.ten_su_kien, u.ho_ten, u.avt as avatar 
                     FROM binh_luan bl
                     JOIN su_kien sk ON bl.ma_su_kien = sk.ma_su_kien
                     JOIN nguoi_dung u ON bl.ma_nguoi_dung = u.ma_nguoi_dung
                     WHERE bl.trang_thai = 'pending'
                     ORDER BY bl.ngay_tao DESC";
            
            try {
                $stmt = $db->prepare($query);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($results) > 0) {
                    error_log('Query 2 successful with ' . count($results) . ' results');
                    return $results;
                }
            } catch (PDOException $e) {
                error_log('Query 2 failed: ' . $e->getMessage());
            }
            
            // Query 3: Using different table structure
            $query = "SELECT bl.*, sk.ten_su_kien, u.ho_ten, u.avt as avatar 
                     FROM binhluan bl
                     JOIN sukien sk ON bl.ma_su_kien = sk.ma_su_kien
                     JOIN nguoidung u ON bl.ma_khach_hang = u.ma_nguoi_dung
                     WHERE bl.trang_thai = 'CHO_DUYET'
                     ORDER BY bl.ngay_tao DESC";
            
            try {
                $stmt = $db->prepare($query);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($results) > 0) {
                    error_log('Query 3 successful with ' . count($results) . ' results');
                    return $results;
                }
            } catch (PDOException $e) {
                error_log('Query 3 failed: ' . $e->getMessage());
            }
            
            // If all queries fail, return empty array
            return [];
            
        } catch (PDOException $e) {
            error_log('Error in getPendingComments: ' . $e->getMessage());
            return [];
        }
    }
    
    private function approveComment($commentId) {
        try {
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Try different table and field names
            try {
                $query = "UPDATE binh_luan SET trang_thai = 'approved' WHERE ma_binh_luan = :comment_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    return true;
                }
            } catch (PDOException $e) {
                error_log('First approve attempt failed: ' . $e->getMessage());
            }
            
            try {
                $query = "UPDATE binhluan SET trang_thai = 'DA_DUYET' WHERE ma_binh_luan = :comment_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                error_log('Second approve attempt failed: ' . $e->getMessage());
                return false;
            }
            
        } catch (PDOException $e) {
            error_log('Error approving comment: ' . $e->getMessage());
            return false;
        }
    }
    
    private function rejectComment($commentId) {
        try {
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Try different table and field names
            try {
                $query = "UPDATE binh_luan SET trang_thai = 'rejected' WHERE ma_binh_luan = :comment_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    return true;
                }
            } catch (PDOException $e) {
                error_log('First reject attempt failed: ' . $e->getMessage());
            }
            
            try {
                $query = "UPDATE binhluan SET trang_thai = 'TU_CHOI' WHERE ma_binh_luan = :comment_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                error_log('Second reject attempt failed: ' . $e->getMessage());
                return false;
            }
            
        } catch (PDOException $e) {
            error_log('Error rejecting comment: ' . $e->getMessage());
            return false;
        }
    }
}
