<?php
require_once __DIR__ . '/../config/database.php';

class Event {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllEvents($limit = null) {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.ma_nha_to_chuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.trang_thai = 'DA_DUYET' 
                 GROUP BY s.ma_su_kien
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
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien 
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.ma_nha_to_chuc 
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
        // Kiểm tra dữ liệu đầu vào
        $requiredFields = ['ten_su_kien', 'ngay_dien_ra', 'gio_dien_ra', 'dia_diem', 'so_luong_cho', 'maloaisukien', 'ma_nha_to_chuc'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Thiếu thông tin bắt buộc: $field");
            }
        }
        if ($data['so_luong_cho'] < 1) {
            throw new Exception("Số lượng chỗ phải lớn hơn 0");
        }

        $query = "INSERT INTO sukien (
            ten_su_kien, ngay_dien_ra, gio_dien_ra, dia_diem, mo_ta, 
            hinh_anh, so_luong_cho, thoi_han_dat_ve, trang_thai_cho_ngoi, 
            trang_thai, maloaisukien, ma_nha_to_chuc
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'CHO_DUYET', ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $data['ten_su_kien'],
            $data['ngay_dien_ra'],
            $data['gio_dien_ra'],
            $data['dia_diem'],
            $data['mo_ta'] ?? null,
            $data['hinh_anh'] ?? null,
            $data['so_luong_cho'],
            $data['thoi_han_dat_ve'] ?? null,
            $data['trang_thai_cho_ngoi'] ?? 'CON_CHO',
            $data['maloaisukien'],
            $data['ma_nha_to_chuc']
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
        $requiredFields = ['ten_su_kien', 'ngay_dien_ra', 'gio_dien_ra', 'dia_diem', 'so_luong_cho', 'maloaisukien', 'ma_nha_to_chuc'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Thiếu thông tin bắt buộc: $field");
            }
        }

        $query = "UPDATE sukien 
                 SET ten_su_kien = ?, ngay_dien_ra = ?, gio_dien_ra = ?, 
                     dia_diem = ?, mo_ta = ?, hinh_anh = ?, so_luong_cho = ?, 
                     thoi_han_dat_ve = ?, trang_thai_cho_ngoi = ?, 
                     maloaisukien = ?, ma_nha_to_chuc = ?,
                     trang_thai = 'CHO_DUYET' 
                 WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ten_su_kien'],
            $data['ngay_dien_ra'],
            $data['gio_dien_ra'],
            $data['dia_diem'],
            $data['mo_ta'] ?? null,
            $data['hinh_anh'] ?? null,
            $data['so_luong_cho'],
            $data['thoi_han_dat_ve'] ?? null,
            $data['trang_thai_cho_ngoi'],
            $data['maloaisukien'],
            $data['ma_nha_to_chuc'],
            $id
        ]);
    }

    public function addTicketType($data) {
        if (empty($data['ma_su_kien']) || empty($data['ten_loai_ve']) || !isset($data['gia_ve'])) {
            throw new Exception("Thiếu thông tin loại vé bắt buộc");
        }
        $query = "INSERT INTO loaive (ma_su_kien, ten_loai_ve, gia_ve, mo_ta) 
                 VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $data['ma_su_kien'],
            $data['ten_loai_ve'],
            $data['gia_ve'],
            $data['mo_ta'] ?? null
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function addSeats($eventId, $seats) {
        if (!is_numeric($eventId) || $eventId < 1 || empty($seats)) {
            throw new Exception("Dữ liệu chỗ ngồi không hợp lệ");
        }
        $query = "INSERT INTO chongoi (ma_su_kien, so_cho, trang_thai, ma_loai_ve) 
                 VALUES (?, ?, 'TRONG', ?)";
        $stmt = $this->db->prepare($query);
        
        foreach ($seats as $seat) {
            if (empty($seat['so_cho']) || empty($seat['ma_loai_ve'])) {
                throw new Exception("Thông tin chỗ ngồi không đầy đủ");
            }
            $stmt->execute([
                $eventId,
                $seat['so_cho'],
                $seat['ma_loai_ve']
            ]);
        }
        return true;
    }

    public function searchEvents($keyword, $category = null, $date = null, $location = null, $price = null) {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.ma_nha_to_chuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
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
                    $query .= " AND t.gia_ve = 0";
                    break;
                case 'paid':
                    $query .= " AND t.gia_ve > 0";
                    break;
            }
        }

        $query .= " GROUP BY s.ma_su_kien ORDER BY s.ngay_dien_ra DESC";
        
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
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.ma_nha_to_chuc 
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
        $sql = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                       MIN(t.gia_ve) as gia_ve_min,
                       MAX(t.gia_ve) as gia_ve_max
                FROM sukien s 
                LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.ma_nha_to_chuc 
                LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
                LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                WHERE s.trang_thai = 'DA_DUYET' 
                GROUP BY s.ma_su_kien
                ORDER BY s.ngay_tao DESC 
                LIMIT 4";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingEvents() {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.ma_nha_to_chuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.trang_thai = 'DA_DUYET'
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_dien_ra DESC
                 LIMIT 4";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByOrganizer($organizerId) {
        if (!is_numeric($organizerId) || $organizerId < 1) {
            return [];
        }
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.ma_nha_to_chuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.ma_nha_to_chuc = ?
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_dien_ra DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$organizerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByType($typeId) {
        if (!is_numeric($typeId) || $typeId < 1) {
            return [];
        }
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.ma_nha_to_chuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.maloaisukien = ? AND s.trang_thai = 'DA_DUYET'
                 GROUP BY s.ma_su_kien
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
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.ma_nha_to_chuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.trang_thai = 'CHO_DUYET'
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_tao DESC";
        
        $stmt = $this->db->prepare($query);
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
            
            // Xóa các bình luận liên quan
            $query1 = "DELETE FROM binhluan WHERE ma_su_kien = ?";
            $stmt1 = $this->db->prepare($query1);
            $stmt1->execute([$id]);
            
            // Xóa các vé đã bán
            $query2 = "DELETE FROM ve WHERE ma_su_kien = ?";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->execute([$id]);
            
            // Xóa các chỗ ngồi
            $query3 = "DELETE FROM chongoi WHERE ma_su_kien = ?";
            $stmt3 = $this->db->prepare($query3);
            $stmt3->execute([$id]);
            
            // Xóa các loại vé
            $query4 = "DELETE FROM loaive WHERE ma_su_kien = ?";
            $stmt4 = $this->db->prepare($query4);
            $stmt4->execute([$id]);
            
            // Xóa sự kiện
            $query5 = "DELETE FROM sukien WHERE ma_su_kien = ?";
            $stmt5 = $this->db->prepare($query5);
            $stmt5->execute([$id]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Lỗi xóa sự kiện: " . $e->getMessage());
        }
    }
}

/* Đề xuất chỉ mục để tối ưu tìm kiếm
CREATE INDEX idx_sukien_search ON sukien(ten_su_kien, dia_diem);
CREATE INDEX idx_sukien_trang_thai ON sukien(trang_thai);
CREATE INDEX idx_loaive_ma_su_kien ON loaive(ma_su_kien);
*/