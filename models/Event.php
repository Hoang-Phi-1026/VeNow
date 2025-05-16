<?php
require_once __DIR__ . '/../config/database.php';

class Event {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllEvents($limit = null) {
        $query = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
                (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
         FROM sukien s 
         LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
         LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
         WHERE s.trang_thai = 'DA_DUYET' 
         ORDER BY s.ngay_dien_ra DESC";
        
        if ($limit) {
            $query .= " LIMIT ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$limit]);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id) {
        if (!is_numeric($id) || $id < 1) {
            return false;
        }
        $query = "SELECT s.*, nd.ho_ten as ten_nha_to_chuc, nd.avt as avatar_nha_to_chuc, l.tenloaisukien,
            (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
            (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
     FROM sukien s 
     LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
     LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
     WHERE s.ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getEventTickets($eventId) {
        if (!is_numeric($eventId) || $eventId < 1) {
            return [];
        }
        $query = "SELECT * FROM loaive WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventSeats($eventId) {
        if (!is_numeric($eventId) || $eventId < 1) {
            return [];
        }
        $query = "SELECT c.*, l.ten_loai_ve, l.gia_ve 
                 FROM chongoi c 
                 LEFT JOIN loaive l ON c.ma_loai_ve = l.ma_loai_ve 
                 WHERE c.ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEvent($data) {
        $requiredFields = ['ten_su_kien', 'ngay_dien_ra', 'gio_dien_ra', 'dia_diem', 'so_luong_cho', 'maloaisukien', 'ma_nguoi_dung'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Thiếu thông tin bắt buộc: $field");
            }
        }
        if ($data['so_luong_cho'] < 1) {
            throw new Exception("Số lượng chỗ phải lớn hơn 0");
        }

        $query = "INSERT INTO sukien (
            ten_su_kien, ngay_dien_ra, gio_dien_ra, ngay_ket_thuc, 
            dia_diem, mo_ta, hinh_anh, so_luong_cho, thoi_han_dat_ve, 
            trang_thai_cho_ngoi, trang_thai, maloaisukien, ma_nguoi_dung
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'CHO_DUYET', ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $data['ten_su_kien'],
            $data['ngay_dien_ra'],
            $data['gio_dien_ra'],
            $data['ngay_ket_thuc'] ?? null,
            $data['dia_diem'],
            $data['mo_ta'] ?? null,
            $data['hinh_anh'] ?? null,
            $data['so_luong_cho'],
            $data['thoi_han_dat_ve'] ?? null,
            $data['trang_thai_cho_ngoi'] ?? 'CON_CHO',
            $data['maloaisukien'],
            $data['ma_nguoi_dung']
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateEvent($id, $data) {
        if (!is_numeric($id) || $id < 1) {
            throw new Exception("Mã sự kiện không hợp lệ");
        }
        $requiredFields = ['ten_su_kien', 'ngay_dien_ra', 'gio_dien_ra', 'dia_diem', 'so_luong_cho', 'maloaisukien', 'ma_nguoi_dung'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Thiếu thông tin bắt buộc: $field");
            }
        }

        // Kiểm tra tổng số ghế của các loại vé
        $query = "SELECT SUM(so_hang * so_cot) as total_seats FROM loaive WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $totalSeats = $stmt->fetchColumn();
        if ($totalSeats > $data['so_luong_cho']) {
            throw new Exception("Tổng số chỗ ngồi của các loại vé vượt quá số lượng chỗ cho phép");
        }

        $query = "UPDATE sukien 
                 SET ten_su_kien = ?, ngay_dien_ra = ?, gio_dien_ra = ?, 
                     ngay_ket_thuc = ?, 
                     dia_diem = ?, mo_t penetrating = ?, hinh_anh = ?, so_luong_cho = ?, 
                     thoi_han_dat_ve = ?, trang_thai_cho_ngoi = ?, 
                     maloaisukien = ?, ma_nguoi_dung = ?,
                     trang_thai = 'CHO_DUYET' 
                 WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ten_su_kien'],
            $data['ngay_dien_ra'],
            $data['gio_dien_ra'],
            $data['ngay_ket_thuc'] ?? null,
            $data['dia_diem'],
            $data['mo_ta'] ?? null,
            $data['hinh_anh'] ?? null,
            $data['so_luong_cho'],
            $data['thoi_han_dat_ve'] ?? null,
            $data['trang_thai_cho_ngoi'],
            $data['maloaisukien'],
            $data['ma_nguoi_dung'],
            $id
        ]);
    }

    public function addTicketType($data) {
        if (empty($data['ma_su_kien']) || empty($data['ten_loai_ve']) || !isset($data['gia_ve']) || empty($data['so_hang']) || empty($data['so_cot'])) {
            throw new Exception("Thiếu thông tin loại vé bắt buộc");
        }
        $query = "INSERT INTO loaive (ma_su_kien, ten_loai_ve, gia_ve, so_hang, so_cot, mo_ta) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $data['ma_su_kien'],
            $data['ten_loai_ve'],
            $data['gia_ve'],
            $data['so_hang'],
            $data['so_cot'],
            $data['mo_ta'] ?? null
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function addSeats($eventId, $seats) {
        if (!is_numeric($eventId) || $eventId < 1 || empty($seats)) {
            error_log("Dữ liệu chỗ ngồi không hợp lệ: eventId=$eventId, seats=" . json_encode($seats));
            throw new Exception("Dữ liệu chỗ ngồi không hợp lệ");
        }
        $query = "INSERT INTO chongoi (ma_su_kien, so_cho, trang_thai, ma_loai_ve) 
                 VALUES (?, ?, 'TRONG', ?)";
        $stmt = $this->db->prepare($query);
        
        foreach ($seats as $seat) {
            if (empty($seat['so_cho']) || empty($seat['ma_loai_ve'])) {
                error_log("Thông tin chỗ ngồi không đầy đủ: " . json_encode($seat));
                throw new Exception("Thông tin chỗ ngồi không đầy đủ");
            }
            error_log("Thêm chỗ ngồi: eventId=$eventId, so_cho={$seat['so_cho']}, ma_loai_ve={$seat['ma_loai_ve']}");
            $stmt->execute([
                $eventId,
                $seat['so_cho'],
                $seat['ma_loai_ve']
            ]);
        }
        return true;
    }

    public function deleteTicketType($ticketId) {
        if (!is_numeric($ticketId) || $ticketId < 1) {
            throw new Exception("Mã loại vé không hợp lệ");
        }

        // Kiểm tra vé đã bán
        $query = "SELECT COUNT(*) FROM ve WHERE ma_loai_ve = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Không thể xóa loại vé đã có vé được bán");
        }

        // Lấy thông tin loại vé
        $query = "SELECT ma_su_kien, so_hang, so_cot FROM loaive WHERE ma_loai_ve = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) {
            throw new Exception("Loại vé không tồn tại");
        }

        // Kiểm tra tổng số ghế còn lại
        $query = "SELECT so_luong_cho FROM sukien WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticket['ma_su_kien']]);
        $so_luong_cho = $stmt->fetchColumn();

        $query = "SELECT SUM(so_hang * so_cot) as total_seats FROM loaive WHERE ma_su_kien = ? AND ma_loai_ve != ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticket['ma_su_kien'], $ticketId]);
        $remainingSeats = $stmt->fetchColumn();

        if ($remainingSeats > $so_luong_cho) {
            throw new Exception("Tổng số chỗ ngồi còn lại vượt quá số lượng chỗ cho phép");
        }

        // Xóa chỗ ngồi liên quan
        $query = "DELETE FROM chongoi WHERE ma_loai_ve = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);

        // Xóa loại vé
        $query = "DELETE FROM loaive WHERE ma_loai_ve = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$ticketId]);
    }

    public function searchEvents($keyword, $category = null, $date = null, $location = null, $price = null) {
        $query = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
                (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
         FROM sukien s 
         LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
         LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
         WHERE s.trang_thai = 'DA_DUYET'";
        
        $params = [];
        
        if (!empty($keyword)) {
            $query .= " AND (s.ten_su_kien LIKE ? OR s.mo_ta LIKE ? OR s.dia_diem LIKE ?)";
            $searchTerm = "%$keyword%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($category)) {
            $query .= " AND s.maloaisukien = ?";
            $params[] = $category;
        }

        if (!empty($date)) {
            $query .= " AND DATE(s.ngay_dien_ra) = ?";
            $params[] = $date;
        }

        if (!empty($location)) {
            $query .= " AND s.dia_diem LIKE ?";
            $params[] = "%$location%";
        }

        if (!empty($price)) {
            switch ($price) {
                case 'free':
                    $query .= " AND EXISTS (SELECT 1 FROM loaive WHERE ma_su_kien = s.ma_su_kien AND gia_ve = 0 AND trang_thai = 'CON_VE')";
                    break;
                case 'paid':
                    $query .= " AND EXISTS (SELECT 1 FROM loaive WHERE ma_su_kien = s.ma_su_kien AND gia_ve > 0 AND trang_thai = 'CON_VE')";
                    break;
            }
        }

        $query .= " ORDER BY s.ngay_dien_ra DESC";
        
        $stmt = $this->db->prepare($query);
        
        if (!empty($params)) {
            for ($i = 0; $i < count($params); $i++) {
                $stmt->bindValue($i + 1, $params[$i]);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function searchEventsForAdmin($keyword = '', $category = '', $status = '') {
        $query = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                MIN(t.gia_ve) as gia_ve_min,
                MAX(t.gia_ve) as gia_ve_max
         FROM sukien s 
         LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
         LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
         LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
         WHERE 1=1";
        
        $params = [];
        
        if (!empty($keyword)) {
            $query .= " AND (s.ten_su_kien LIKE ? OR s.mo_ta LIKE ? OR s.dia_diem LIKE ?)";
            $searchTerm = "%$keyword%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($category)) {
            $query .= " AND s.maloaisukien = ?";
            $params[] = $category;
        }
        
        if (!empty($status)) {
            $query .= " AND s.trang_thai = ?";
            $params[] = $status;
        }
        
        $query .= " GROUP BY s.ma_su_kien ORDER BY s.ngay_tao DESC";
        
        $stmt = $this->db->prepare($query);
        
        if (!empty($params)) {
            for ($i = 0; $i < count($params); $i++) {
                $stmt->bindValue($i + 1, $params[$i]);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeaturedEvents() {
        $sql = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
                (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
                FROM sukien s 
                LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
                LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
                WHERE s.trang_thai = 'DA_DUYET' 
                ORDER BY s.ngay_dien_ra DESC 
                LIMIT 6";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingEvents() {
        $sql = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
                (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
                FROM sukien s 
                LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
                LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
                WHERE s.trang_thai = 'DA_DUYET' 
                AND s.ngay_dien_ra >= CURDATE() 
                ORDER BY s.ngay_dien_ra ASC 
                LIMIT 6";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPastEvents() {
        $sql = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
                (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
                FROM sukien s 
                LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
                LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
                WHERE s.trang_thai = 'DA_DUYET' 
                AND s.ngay_dien_ra < CURDATE() 
                ORDER BY s.ngay_dien_ra DESC 
                LIMIT 6";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByOrganizer($organizerId) {
        if (!is_numeric($organizerId) || $organizerId < 1) {
            return [];
        }
        $sql = "SELECT s.*, nd.ho_ten, l.tenloaisukien 
        FROM sukien s 
        LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
        LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
        WHERE s.ma_nguoi_dung = ? 
        ORDER BY s.ngay_dien_ra DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$organizerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByType($typeId) {
        if (!is_numeric($typeId) || $typeId < 1) {
            return [];
        }
        $query = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                        (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
                        (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 WHERE s.maloaisukien = ? AND s.trang_thai = 'DA_DUYET'
                 ORDER BY s.ngay_dien_ra DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$typeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventCategories() {
        $query = "SELECT * FROM loaisukien ORDER BY tenloaisukien ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllEventTypes() {
        $query = "SELECT * FROM loaisukien ORDER BY tenloaisukien ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingEvents() {
        $sql = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                        (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
                        (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
                FROM sukien s 
                LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
                LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
                WHERE s.trang_thai = 'CHO_DUYET' 
                ORDER BY s.ngay_dien_ra DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovedEvents() {
        $sql = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                        (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
                        (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
                FROM sukien s 
                LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
                LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
                WHERE s.trang_thai = 'DA_DUYET' 
                ORDER BY s.ngay_dien_ra DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRejectedEvents() {
        $sql = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                        (SELECT MIN(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_min,
                        (SELECT MAX(gia_ve) FROM loaive WHERE ma_su_kien = s.ma_su_kien AND trang_thai = 'CON_VE') as gia_ve_max
                FROM sukien s 
                LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
                LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
                WHERE s.trang_thai = 'TU_CHOI' 
                ORDER BY s.ngay_dien_ra DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEventStatus($eventId, $status) {
        if (!is_numeric($eventId) || $eventId < 1 || !in_array($status, ['CHO_DUYET', 'DA_DUYET', 'TU_CHOI', 'DA_HUY'])) {
            throw new Exception("Dữ liệu trạng thái hoặc mã sự kiện không hợp lệ");
        }
        $query = "UPDATE sukien SET trang_thai = ? WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $eventId]);
    }
    
    public function deleteEvent($id) {
        if (!is_numeric($id) || $id < 1) {
            throw new Exception("Mã sự kiện không hợp lệ");
        }
        try {
            $this->db->beginTransaction();
            
            // Kiểm tra vé đã bán
            $query = "SELECT COUNT(*) FROM ve WHERE ma_su_kien = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Không thể xóa sự kiện có vé đã bán");
            }
            
            // Kiểm tra xem có bảng dangkythamgia không
            $checkTableQuery = "SHOW TABLES LIKE 'dangkythamgia'";
            $checkTableStmt = $this->db->prepare($checkTableQuery);
            $checkTableStmt->execute();
            if ($checkTableStmt->rowCount() > 0) {
                // Xóa các đăng ký tham gia
                $query0 = "DELETE FROM dangkythamgia WHERE ma_su_kien = ?";
                $stmt0 = $this->db->prepare($query0);
                $result0 = $stmt0->execute([$id]);
                if (!$result0) {
                    throw new Exception("Lỗi khi xóa đăng ký tham gia: " . implode(", ", $stmt0->errorInfo()));
                }
            }
            
            // Xóa các bình luận liên quan
            $query1 = "DELETE FROM binhluan WHERE ma_su_kien = ?";
            $stmt1 = $this->db->prepare($query1);
            $result1 = $stmt1->execute([$id]);
            if (!$result1) {
                throw new Exception("Lỗi khi xóa bình luận: " . implode(", ", $stmt1->errorInfo()));
            }
            
            // Xóa các vé đã bán
            $query2 = "DELETE FROM ve WHERE ma_su_kien = ?";
            $stmt2 = $this->db->prepare($query2);
            $result2 = $stmt2->execute([$id]);
            if (!$result2) {
                throw new Exception("Lỗi khi xóa vé: " . implode(", ", $stmt2->errorInfo()));
            }
            
            // Xóa các chỗ ngồi
            $query3 = "DELETE FROM chongoi WHERE ma_su_kien = ?";
            $stmt3 = $this->db->prepare($query3);
            $result3 = $stmt3->execute([$id]);
            if (!$result3) {
                throw new Exception("Lỗi khi xóa chỗ ngồi: " . implode(", ", $stmt3->errorInfo()));
            }
            
            // Xóa các loại vé
            $query4 = "DELETE FROM loaive WHERE ma_su_kien = ?";
            $stmt4 = $this->db->prepare($query4);
            $result4 = $stmt4->execute([$id]);
            if (!$result4) {
                throw new Exception("Lỗi khi xóa loại vé: " . implode(", ", $stmt4->errorInfo()));
            }
            
            // Xóa các yêu cầu sự kiện liên quan
            $query5 = "DELETE FROM yeucausukien WHERE ma_su_kien = ?";
            $stmt5 = $this->db->prepare($query5);
            $result5 = $stmt5->execute([$id]);
            if (!$result5) {
                throw new Exception("Lỗi khi xóa yêu cầu sự kiện: " . implode(", ", $stmt5->errorInfo()));
            }
            
            // Kiểm tra xem có bảng thongbao không
            $checkTableQuery = "SHOW TABLES LIKE 'thongbao'";
            $checkTableStmt = $this->db->prepare($checkTableQuery);
            $checkTableStmt->execute();
            if ($checkTableStmt->rowCount() > 0) {
                // Xóa các thông báo liên quan
                $query6 = "DELETE FROM thongbao WHERE noi_dung LIKE ?";
                $stmt6 = $this->db->prepare($query6);
                $result6 = $stmt6->execute(['%"ma_su_kien":"' . $id . '"%']);
                if (!$result6) {
                    throw new Exception("Lỗi khi xóa thông báo: " . implode(", ", $stmt6->errorInfo()));
                }
            }
            
            // Xóa sự kiện
            $query7 = "DELETE FROM sukien WHERE ma_su_kien = ?";
            $stmt7 = $this->db->prepare($query7);
            $result7 = $stmt7->execute([$id]);
            if (!$result7) {
                throw new Exception("Lỗi khi xóa sự kiện: " . implode(", ", $stmt7->errorInfo()));
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi xóa sự kiện ID $id: " . $e->getMessage());
            throw new Exception("Lỗi xóa sự kiện: " . $e->getMessage());
        }
    }
}

/* Đề xuất chỉ mục để tối ưu tìm kiếm
CREATE INDEX idx_sukien_search ON sukien(ten_su_kien, dia_diem);
CREATE INDEX idx_sukien_trang_thai ON sukien(trang_thai);
CREATE INDEX idx_loaive_ma_su_kien ON loaive(ma_su_kien);
CREATE INDEX idx_sukien_ngay_ket_thuc ON sukien(ngay_ket_thuc);
CREATE INDEX idx_loaive_so_hang_cot ON loaive(so_hang, so_cot);
CREATE INDEX idx_chongoi_ma_su_kien ON chongoi(ma_su_kien);
CREATE INDEX idx_chongoi_ma_loai_ve ON chongoi(ma_loai_ve);
*/