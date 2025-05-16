<?php
require_once __DIR__ . '/../config/database.php';

class Comment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy danh sách bình luận đã duyệt của một sự kiện
     */
    public function getApprovedCommentsByEventId($eventId) {
        error_log("Fetching approved comments for event ID: " . $eventId);
        try {
            $query = "SELECT b.*, n.ho_ten, n.avt as avatar 
                     FROM binhluan b 
                     LEFT JOIN nguoidung n ON b.ma_khach_hang = n.ma_nguoi_dung 
                     WHERE b.ma_su_kien = ? AND b.trang_thai = 'DA_DUYET'
                     ORDER BY b.ngay_tao DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$eventId]);
            
            // Debug
            error_log("Query: " . $query);
            error_log("Event ID: " . $eventId);
            error_log("Comments found: " . $stmt->rowCount());
            
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Comments data: " . print_r($comments, true));
            
            return $comments;
        } catch (PDOException $e) {
            error_log("Lỗi khi lấy bình luận: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tổng số bình luận đã được duyệt của một sự kiện
     */
    public function getApprovedCommentCount($eventId) {
        $query = "SELECT COUNT(*) as count FROM binhluan WHERE ma_su_kien = ? AND trang_thai = 'DA_DUYET'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$eventId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    /**
     * Lấy điểm đánh giá trung bình của một sự kiện
     */
    public function getAverageRating($eventId) {
        $query = "SELECT AVG(diem_danh_gia) as avg_rating FROM binhluan WHERE ma_su_kien = ? AND trang_thai = 'DA_DUYET'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$eventId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
    }

    /**
     * Lấy phân bố điểm đánh giá của một sự kiện
     */
    public function getRatingDistribution($eventId) {
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $query = "SELECT COUNT(*) as count FROM binhluan WHERE ma_su_kien = ? AND diem_danh_gia = ? AND trang_thai = 'DA_DUYET'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$eventId, $i]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $distribution[$i] = $result['count'];
        }
        return $distribution;
    }

    /**
     * Thêm bình luận mới
     */
    public function addComment($eventId, $userId, $content, $rating) {
        try {
            // Debug thông tin đầu vào
            error_log("Thêm bình luận - Event ID: $eventId, User ID: $userId, Rating: $rating");
            
            $query = "INSERT INTO binhluan (ma_su_kien, ma_khach_hang, noi_dung, diem_danh_gia, trang_thai) 
                     VALUES (?, ?, ?, ?, 'CHO_DUYET')";
            $stmt = $this->db->prepare($query);
            
            // Debug câu query
            error_log("Query: $query");
            error_log("Params: " . print_r([$eventId, $userId, $content, $rating], true));
            
            $result = $stmt->execute([$eventId, $userId, $content, $rating]);
            
            if (!$result) {
                error_log("Lỗi khi thêm bình luận: " . print_r($stmt->errorInfo(), true));
                return false;
            }
            
            error_log("Thêm bình luận thành công");
            return true;
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi thêm bình luận: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra xem người dùng đã bình luận cho sự kiện này chưa
     */
    public function hasUserCommented($eventId, $userId) {
        try {
            $query = "SELECT COUNT(*) as count FROM binhluan WHERE ma_su_kien = ? AND ma_khach_hang = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$eventId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Lỗi khi kiểm tra bình luận: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra trạng thái bình luận của người dùng
     */
    public function getUserCommentStatus($eventId, $userId) {
        try {
            $query = "SELECT trang_thai FROM binhluan WHERE ma_su_kien = ? AND ma_khach_hang = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$eventId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['trang_thai'] : null;
        } catch (PDOException $e) {
            error_log("Lỗi khi kiểm tra trạng thái bình luận: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách bình luận chờ duyệt
     */
    public function getPendingComments() {
        try {
            $query = "SELECT b.*, n.ho_ten, n.avt as avatar, s.ten_su_kien, s.ma_su_kien
                     FROM binhluan b 
                     LEFT JOIN nguoidung n ON b.ma_khach_hang = n.ma_nguoi_dung 
                     LEFT JOIN sukien s ON b.ma_su_kien = s.ma_su_kien
                     WHERE b.trang_thai = 'CHO_DUYET'
                     ORDER BY b.ngay_tao DESC";
        
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi khi lấy bình luận chờ duyệt: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Duyệt bình luận
     */
    public function approveComment($commentId) {
        try {
            error_log("Bắt đầu duyệt bình luận ID: " . $commentId);
            
            // Kiểm tra xem bình luận có tồn tại không
            $checkQuery = "SELECT ma_binh_luan FROM binhluan WHERE ma_binh_luan = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute([$commentId]);
            
            if ($checkStmt->rowCount() === 0) {
                error_log("Không tìm thấy bình luận ID: " . $commentId);
                return false;
            }
            
            $query = "UPDATE binhluan SET trang_thai = 'DA_DUYET' WHERE ma_binh_luan = ?";
            $stmt = $this->db->prepare($query);
            
            error_log("Query: " . $query);
            error_log("Comment ID: " . $commentId);
            
            $result = $stmt->execute([$commentId]);
            
            if (!$result) {
                error_log("Lỗi khi duyệt bình luận: " . print_r($stmt->errorInfo(), true));
                return false;
            }
            
            error_log("Duyệt bình luận thành công");
            return true;
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi duyệt bình luận: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Từ chối bình luận
     */
    public function rejectComment($commentId) {
        try {
            error_log("Bắt đầu từ chối bình luận ID: " . $commentId);
            
            // Kiểm tra xem bình luận có tồn tại không
            $checkQuery = "SELECT ma_binh_luan FROM binhluan WHERE ma_binh_luan = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute([$commentId]);
            
            if ($checkStmt->rowCount() === 0) {
                error_log("Không tìm thấy bình luận ID: " . $commentId);
                return false;
            }
            
            $query = "UPDATE binhluan SET trang_thai = 'TU_CHOI' WHERE ma_binh_luan = ?";
            $stmt = $this->db->prepare($query);
            
            error_log("Query: " . $query);
            error_log("Comment ID: " . $commentId);
            
            $result = $stmt->execute([$commentId]);
            
            if (!$result) {
                error_log("Lỗi khi từ chối bình luận: " . print_r($stmt->errorInfo(), true));
                return false;
            }
            
            error_log("Từ chối bình luận thành công");
            return true;
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi từ chối bình luận: " . $e->getMessage());
            return false;
        }
    }
}
